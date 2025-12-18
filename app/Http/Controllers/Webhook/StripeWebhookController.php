<?php

namespace App\Http\Controllers\Webhook;

use App\Helper\CRM;
use App\Http\Controllers\Controller;
use App\Models\Agency;
use App\Models\Home;
use App\Models\Inspection;
use App\Models\User;
use App\Models\WebhookLog;
use App\Services\CrmService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class StripeWebhookController extends Controller
{
    /**
     * Handle incoming webhooks from Stripe.
     */
    public function handle(Request $request)
    {
        $payload = $request->all();
        $eventType = $payload['type'] ?? 'unknown';
        $log = WebhookLog::create([
            'source' => 'stripe',
            'event_type' => $eventType,
            'payload' => json_encode($payload),
        ]);
        if ($eventType === 'customer.subscription.created') {
            try {
                $this->handleSubscriptionCreated($payload['data']['object']);
                $log->update(['status' => 'processed']);
            } catch (\Exception $e) {
                dd($e->getMessage());
                $log->update(['status' => 'failed', 'notes' => $e->getMessage()]);
                return response()->json(['error' => 'Webhook processing failed.'], 500);
            }
        }

        return response()->json(['status' => 'success']);
    }

    /**
     * Process the 'customer.subscription.created' event.
     *
     * @param array $subscriptionData
     */
    protected function handleSubscriptionCreated(array $subscriptionData)
    {
        $customerEmail = $subscriptionData['customer_details']['email'];
        $customerName = $subscriptionData['customer_details']['name'];
        $agencyId = $subscriptionData['metadata']['agency_id'] ?? null;
        if (!$agencyId) {
            throw new \Exception("Agency ID not found in subscription metadata for customer: {$customerEmail}");
        }

        $agency = Agency::find($agencyId);
        if (!$agency) {
            throw new \Exception("Agency with ID {$agencyId} not found.");
        }


        $locationId = CRM::getDefault('homeowner_clone_location_id', '', $agency);
        $userId = CRM::getDefault('homeowner_clone_user_id', '', $agency);

        $crmService = new CrmService($agency);
        $crmUsersResponse = $crmService->getCrmUserById($userId, $locationId);
        $userData = [
            'companyId' => $agency->crmToken?->company_id,
            'firstName' => $customerName,
            'lastName' => $customerName,
            'email' => $customerEmail,
            'password' => Hash::make('password123!'),
            'role' => 'homeowner',
            'type' => $crmUsersResponse->roles->type,
            'role' => $crmUsersResponse->roles->role,
            'locationIds' => [$locationId],
            'permissions' => $crmUsersResponse->permissions,
            'scopes'    => $crmUsersResponse->scopes,
            'scopesAssignedToOnly' => $crmUsersResponse->scopesAssignedToOnly,

        ];
        $newusersResponse = $crmService->createUser($userData, $locationId);
        $user = User::where('email', $customerEmail)->where('agency_id', $agency->id)->first();

        if (!$user) {
            $user = User::create([
                'name' => $customerName,
                'email' => $customerEmail,
                'password' => Hash::make('password123!'), // Generate a random password
                'role' => 'homeowner',
                'agency_id' => $agency->id,
                'crm_location_id' => $locationId,
                'crm_user_id' => $newusersResponse->id,
            ]);
        }

        $home = new Home();
        $home->user_id = $user->id;
        $home->nickname = 'Primary Residence';
        $home->address_line1 = $subscriptionData['customer_details']['address']['line1'] ?? 'Address not provided';
        $home->address_line2 = $subscriptionData['customer_details']['address']['line2'] ?? 'Address not provided';
        $home->city = $subscriptionData['customer_details']['address']['city'] ?? 'City not provided';
        $home->state = $subscriptionData['customer_details']['address']['state'] ?? 'State not provided';
        $home->postal_code = $subscriptionData['customer_details']['address']['postal_code'] ?? '00000';
        $home->country = $subscriptionData['customer_details']['address']['country'] ?? 'Country not provided';
        $home->latitude = $subscriptionData['customer_details']['address']['latitude'] ?? null;
        $home->longitude = $subscriptionData['customer_details']['address']['longitude'] ?? null;
        $home->year_built = null;
        $home->square_feet = null;
        $home->roof_type = null;
        $home->roof_material = null;
        $home->roof_age = null;
        $home->hvac_age = null;
        $home->extra = [];
        $home->save();

        Inspection::create([
            'home_id' => $home->id,
            'homeowner_id' => $user->id,
            'agency_id' => $agency->id,
            'status' => 'pending_schedule',
            'trigger_type' => 'initial_subscription',
            'property_address' => null,
            'zip_code' => null,
        ]);
    }
}
