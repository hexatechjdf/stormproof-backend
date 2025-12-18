<?php

namespace App\Http\Controllers;

use App\Models\Agency;
use App\Models\User;
use App\Services\CrmService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class OAuthController extends Controller
{
    protected CrmService $crmService;
    public function __construct(CrmService $crmService)
    {
        $this->crmService = $crmService;
    }
    /**
     * Handle the callback from the service after authorization.
     */
    public function callback(Request $request, $provider)
    {
        $state = $request->input('state');
        $decoded = json_decode(base64_decode($state), true);
        $agencyId = $decoded['agency_id'] ?? null;
        $agency = Agency::where('id', $agencyId)->first();
        $code = $request->input('code');
        switch ($provider) {
            case 'stripe':
                $this->handleStripeCallback($agency, $code);
                break;
            case 'crm':
                $this->crmService->handleCrmAuth($code, $agency);
                break;
        }

        // Clear session data and redirect back to the settings page
        $request->session()->forget(['oauth_state', 'oauth_agency_id']);
        return redirect()->route('admin.settings.index', $agency)
            ->with('success', ucfirst($provider) . ' connected successfully!');
    }
    private function handleStripeCallback(Agency $agency, $code)
    {
        $response = Http::asForm()->post('https://connect.stripe.com/oauth/token', [
            'client_secret' => config('services.stripe.client_secret'),
            'code' => $code,
            'grant_type' => 'authorization_code',
        ]);

        if ($response->successful()) {
            $agency->settings()->updateOrCreate(
                ['key' => 'stripe_user_id'],
                ['value' => $response->json()['stripe_user_id']]
            );
            // Store the refresh token securely if needed for long-term access
        }
    }

    private function handleCompanyCamCallback(Agency $agency, $code)
    {
        $response = Http::asForm()->post('https://companycam.com/oauth/token', [
            'client_id' => config('services.companycam.client_id'),
            'client_secret' => config('services.companycam.client_secret'),
            'code' => $code,
            'grant_type' => 'authorization_code',
            'redirect_uri' => route('oauth.callback', 'companycam'),
        ]);

        if ($response->successful()) {
            $agency->settings()->updateOrCreate(
                ['key' => 'companycam_access_token'],
                ['value' => $response->json()['access_token']]
            );
            // Store the refresh token
            $agency->settings()->updateOrCreate(
                ['key' => 'companycam_refresh_token'],
                ['value' => $response->json()['refresh_token']]
            );
        }
    }
    private function handleCrmCallback()
    {
        $response = Http::asForm()->post('https://companycam.com/oauth/token', [
            'client_id' => config('services.companycam.client_id'),
            'client_secret' => config('services.companycam.client_secret'),
            'code' => $code,
            'grant_type' => 'authorization_code',
            'redirect_uri' => route('oauth.callback', 'companycam'),
        ]);

        if ($response->successful()) {
            $agency->settings()->updateOrCreate(
                ['key' => 'companycam_access_token'],
                ['value' => $response->json()['access_token']]
            );
            // Store the refresh token
            $agency->settings()->updateOrCreate(
                ['key' => 'companycam_refresh_token'],
                ['value' => $response->json()['refresh_token']]
            );
        }
    }
}
