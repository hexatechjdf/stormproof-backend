<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Agency;
use App\Models\Setting;
use App\Services\CrmService;
use Illuminate\Http\Request;

class SystemSettingsController extends Controller
{
    public function index(Agency $agency)
    {
        $agency->load('settings');
        $settings = $agency->settings->pluck('value', 'key');

        // --- Prepare OAuth Connection URL ---
        $queryParams = http_build_query([
            'response_type' => 'code',
            'redirect_uri' => route('crm.oauth_callback', 'crm'),
            'client_id' => $settings['crm_client_id'] ?? '',
            'scope' => 'users.readonly locations.readonly', // Example scopes
        ]);
        $connectUrl = 'https://marketplace.gohighlevel.com/oauth/chooselocation?' . $queryParams;

        // These would be fetched after a successful OAuth connection
        $company_name = $settings['crm_company_name'] ?? null;
        $company_id = $settings['crm_company_id'] ?? null;

        // The original setting keys for API keys
        $settingKeys = [
            'stripe_api_key',
            'stripe_webhook_secret',
            'gohighlevel_api_key', // This might be deprecated in favor of OAuth tokens
            'companycam_api_key',
        ];
        $crmService = new CrmService();
        $crmLocations = $crmService->getLocations();
        $crmUsers = $crmService->getUsers();
        return view('superadmin.settings.index', compact('crmLocations', 'crmUsers', 'agency', 'settings', 'settingKeys', 'connectUrl', 'company_name', 'company_id'));
    }

    /**
     * Update the settings for a given agency.
     */
    public function update(Request $request, Agency $agency)
    {
        $validated = $request->validate([
            'settings' => 'required|array',
            // Add validation for the new CRM fields
            'settings.crm_client_id' => 'nullable|string',
            'settings.crm_client_secret' => 'nullable|string',
            // Keep old validation
            'settings.stripe_api_key' => 'nullable|string',
            'settings.stripe_webhook_secret' => 'nullable|string',
            'settings.gohighlevel_api_key' => 'nullable|string',
            'settings.companycam_api_key' => 'nullable|string',
        ]);

        foreach ($validated['settings'] as $key => $value) {
            $agency->settings()->updateOrCreate(
                ['key' => $key],
                ['value' => $value ?? '']
            );
        }

        return redirect()->route('superadmin.agencies.settings.index', $agency)
            ->with('success', 'Agency settings updated successfully.');
    }
    public function userMapping()
    {
        $crmService = new CrmService();
        $crmLocations = $crmService->getLocations();
        $crmUsers = $crmService->getUsers();
        return view('superadmin.settings.user-mapping', compact('crmLocations', 'crmUsers'));
    }
}
