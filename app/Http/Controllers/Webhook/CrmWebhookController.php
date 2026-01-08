<?php

namespace App\Http\Controllers\Webhook;

use App\Helper\CRM;
use App\Http\Controllers\Controller;
use App\Models\Agency;
use App\Models\Home;
use App\Models\Inspection;
use App\Models\Setting;
use App\Models\User;
use App\Models\WebhookLog;
use App\Services\CrmService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class CrmWebhookController extends Controller
{
    public function handle(Request $request)
    {
        $payload = $request->all();
        $eventType = $payload['type'] ?? 'unknown';

        $log = WebhookLog::create([
            'source' => 'crm',
            'event_type' => $eventType,
            'payload' => json_encode($payload),
        ]);

        $paymentData = $payload['payment'] ?? null;
        $contactData = $payload['contact'] ?? null;

        if (!$paymentData || !$contactData) {
            $log->update(['status' => 'ignored', 'notes' => 'No payment/contact data']);
            return response()->json(['status' => 'ignored']);
        }

        try {
            $this->handlePaymentWebhook($payload);
            $log->update(['status' => 'processed']);
        } catch (\Exception $e) {
            $log->update(['status' => 'failed', 'notes' => $e->getMessage()]);
            return response()->json(['error' => 'Webhook processing failed.'], 500);
        }

        return response()->json(['status' => 'success']);
    }

    protected function handlePaymentWebhookOLD(array $payload)
    {
        $contactEmail = $payload['email'] ?? $payload['contact']['email'] ?? null;
        $contactName = $payload['full_name'] ?? $payload['contact']['name'] ?? null;
        $firstName = $payload['first_name'];
        $lastName = $payload['last_name'];
        $locationId = $payload['location']['id'] ?? null;

        if (!$locationId) {
            throw new \Exception("Location ID not found in payload for contact: {$contactEmail}");
        }

        $setting = Setting::where('key', 'primary_location')->where('value', $locationId)->first();
        $agencyId = $setting->agency_id;
        $agency = Agency::where('id', $agencyId)->first();
        if (!$agency) {
            throw new \Exception("No agency found for location ID: {$locationId}");
        }

        $settingsMapping = CRM::getDefault('homeowner_product_prices', [], $agency); // JSON stored
        $settingsMapping = is_array($settingsMapping) ? $settingsMapping : json_decode($settingsMapping, true);
        $allMappedPriceIds = [];
        foreach ($settingsMapping as $productId => $priceIds) {
            if (is_array($priceIds)) {
                $allMappedPriceIds = array_merge($allMappedPriceIds, $priceIds);
            }
        }
        $purchasedPriceIds = $payload['payment']['global_product_price_ids'] ?? [];
        $matchFound = false;
        foreach ($purchasedPriceIds as $priceId) {
            if (in_array($priceId, $allMappedPriceIds ?? [])) {
                $matchFound = true;
                break;
            }
        }
        if (!$matchFound) {
            return;
        }

        $templateUserId = CRM::getDefault('homeowner_clone_user_id', null, $agency);
        $templateLocationId = CRM::getDefault('homeowner_clone_location_id', null, $agency);
        $crmService = new CrmService($agency);
        $crmUserTemplate = $crmService->getCrmUserById($templateUserId, $templateLocationId);
        $userData = [
            'companyId' => $agency->crmToken?->company_id,
            'firstName' => $firstName,
            'lastName' => $lastName,
            'email' => $contactEmail,
            'password' => Hash::make('password'),
            'role' => 'homeowner',
            'type' => $crmUserTemplate->roles->type ?? 'user',
            'role' => $crmUserTemplate->roles->role ?? 'user',
            'locationIds' => [$templateLocationId],
            'permissions' => $crmUserTemplate->permissions ?? [],
            'scopes'    => $crmUserTemplate->scopes ?? [],
            'scopesAssignedToOnly' => $crmUserTemplate->scopesAssignedToOnly ?? [],
        ];
        $newCrmUser = $crmService->createUser($userData, $templateLocationId);

        $user = User::firstOrCreate(
            ['email' => $contactEmail, 'agency_id' => $agency->id],
            [
                'name' => $contactName,
                'password' => Hash::make('password'),
                'role' => 'homeowner',
                'crm_location_id' => $templateLocationId,
                'crm_user_id' => $newCrmUser->id,
            ]
        );

        $home = Home::create([
            'user_id' => $user->id,
            'nickname' => 'Primary Residence',
            'address_line1' => $payload['address1'] ?? 'Address not provided',
            'address_line2' => '',
            'city' => $payload['city'] ?? 'City not provided',
            'state' => $payload['state'] ?? 'State not provided',
            'postal_code' => $payload['postal_code'] ?? '00000',
            'country' => $payload['country'] ?? 'Country not provided',
        ]);

        Inspection::create([
            'home_id' => $home->id,
            'homeowner_id' => $user->id,
            'agency_id' => $agency->id,
            'status' => 'pending_schedule',
            'trigger_type' => 'initial_subscription',
        ]);
    }
    protected function handlePaymentWebhook(array $payload)
    {
        $contactEmail = $payload['email'] ?? $payload['contact']['email'] ?? null;
        $contactName  = $payload['full_name'] ?? $payload['contact']['name'] ?? null;
        $firstName    = $payload['first_name'] ?? null;
        $lastName     = $payload['last_name'] ?? null;
        $locationId   = $payload['location']['id'] ?? null;

        if (!$locationId) {
            throw new \Exception("Location ID not found in payload for contact: {$contactEmail}");
        }

        $setting = Setting::where('key', 'primary_location')->where('value', $locationId)->first();
        $agencyId = $setting->agency_id;
        $agency   = Agency::where('id', $agencyId)->first();
        if (!$agency) {
            throw new \Exception("No agency found for location ID: {$locationId}");
        }

        $purchasedPriceIds = $payload['payment']['global_product_price_ids'] ?? [];

        // --- HOMEOWNER LOGIC ---
        $this->createUserFromMapping($agency, 'homeowner', $purchasedPriceIds, $payload, $contactEmail, $contactName, $firstName, $lastName);

        // --- ADVISOR LOGIC ---
        $this->createUserFromMapping($agency, 'advisor', $purchasedPriceIds, $payload, $contactEmail, $contactName, $firstName, $lastName);
    }
    protected function createUserFromMapping(
        Agency $agency,
        string $role,
        array $purchasedPriceIds,
        array $payload,
        string $contactEmail,
        string $contactName,
        ?string $firstName,
        ?string $lastName
    ) {
        $productPricesKey = $role . '_product_prices';          // homeowner_product_prices / advisor_product_prices
        $templateUserKey  = $role . '_clone_user_id';           // homeowner_clone_user_id / advisor_clone_user_id
        $templateLocationKey = $role . '_clone_location_id';    // homeowner_clone_location_id / advisor_clone_location_id

        $settingsMapping = CRM::getDefault($productPricesKey, [], $agency);
        $settingsMapping = is_array($settingsMapping) ? $settingsMapping : json_decode($settingsMapping, true);

        $allMappedPriceIds = [];
        foreach ($settingsMapping as $productId => $priceIds) {
            if (is_array($priceIds)) {
                $allMappedPriceIds = array_merge($allMappedPriceIds, $priceIds);
            }
        }

        // Check if any purchased price matches this role mapping
        $matchFound = false;
        foreach ($purchasedPriceIds as $priceId) {
            if (in_array($priceId, $allMappedPriceIds)) {
                $matchFound = true;
                break;
            }
        }
        if (!$matchFound) {
            return; // skip this role
        }

        $templateUserId     = CRM::getDefault($templateUserKey, null, $agency);
        $templateLocationId = CRM::getDefault($templateLocationKey, null, $agency);

        $crmService = new CrmService($agency);
        $crmUserTemplate = $crmService->getCrmUserById($templateUserId, $templateLocationId);

        $userData = [
            'companyId' => $agency->crmToken?->company_id,
            'firstName' => $firstName,
            'lastName'  => $lastName,
            'email'     => $contactEmail,
            'password'  => Hash::make('password'),
            'role'      => $role,
            'type'      => $crmUserTemplate->roles->type ?? 'user',
            'role'      => $crmUserTemplate->roles->role ?? 'user',
            'locationIds' => [$templateLocationId],
            'permissions' => $crmUserTemplate->permissions ?? [],
            'scopes'    => $crmUserTemplate->scopes ?? [],
            'scopesAssignedToOnly' => $crmUserTemplate->scopesAssignedToOnly ?? [],
        ];

        $newCrmUser = $crmService->createUser($userData, $templateLocationId);

        // Create local user
        $user = User::firstOrCreate(
            ['email' => $contactEmail, 'agency_id' => $agency->id],
            [
                'name' => $contactName,
                'password' => Hash::make('password'),
                'role' => $role,
                'crm_location_id' => $templateLocationId,
                'crm_user_id' => $newCrmUser->id,
            ]
        );

        // Only create a Home record for homeowners
        if ($role === 'homeowner') {
            $home = Home::create([
                'user_id' => $user->id,
                'nickname' => 'Primary Residence',
                'address_line1' => $payload['address1'] ?? 'Address not provided',
                'address_line2' => '',
                'city' => $payload['city'] ?? 'City not provided',
                'state' => $payload['state'] ?? 'State not provided',
                'postal_code' => $payload['postal_code'] ?? '00000',
                'country' => $payload['country'] ?? 'Country not provided',
            ]);

            Inspection::create([
                'home_id' => $home->id,
                'homeowner_id' => $user->id,
                'agency_id' => $agency->id,
                'status' => 'pending_schedule',
                'trigger_type' => 'initial_subscription',
            ]);
        }
    }
}
