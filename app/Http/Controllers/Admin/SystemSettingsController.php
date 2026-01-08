<?php

namespace App\Http\Controllers\Admin;

use App\Helper\CRM;
use App\Http\Controllers\Controller;
use App\Models\Agency;
use App\Models\Setting;
use App\Services\CrmService;
use Illuminate\Container\Attributes\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class SystemSettingsController extends Controller
{
    public function index(Agency $agency)
    {
        $agency->load('settings');
        $settings = $agency->settings->pluck('value', 'key');

        $state = base64_encode(json_encode([
            'agency_id' => $agency->id,
        ]));
        $queryParams = http_build_query([

            'response_type' => 'code',
            'redirect_uri' => route('crm.oauth_callback', 'crm'),
            'client_id' => $settings['crm_client_id'] ?? '',
            'scope' => 'products.readonly products/prices.readonly medias.write medias.readonly calendars.readonly marketplace-installer-details.readonly users.readonly users.write companies.readonly oauth.write calendars/events.write calendars/events.readonly contacts.write contacts.readonly oauth.readonly locations.readonly',
            'state' => $state,
        ]);
        $connectUrl = 'https://marketplace.gohighlevel.com/oauth/chooselocation?' . $queryParams;
        $company_id = $company_name = '';
        if (!empty($agency->crmToken?->company_id)) {
            [$company_name, $company_id] = CRM::getCompany($agency);
        }
        $settingKeys = [
            'stripe_api_key',
            'stripe_webhook_secret',
            'gohighlevel_api_key',
            'companycam_api_key',
            'primary_location_id',
            'primary_calendar_id',
        ];
        $crmLocations = [];
        if (!empty($agency->crmToken)) {
            $crmService = new CrmService($agency);
            $crmLocationsResponse = $crmService->getLocations();

            if (!empty($crmLocationsResponse) && property_exists($crmLocationsResponse, 'locations')) {

                $crmLocations = $crmLocationsResponse->locations;
            }
        }
        $crmCalendars = [];

        return view('admin.settings.index', compact('crmCalendars', 'crmLocations', 'agency', 'settings', 'settingKeys', 'connectUrl', 'company_name', 'company_id'));
    }

    /**
     * Update the settings for a given agency.
     */
    public function update(Request $request, Agency $agency)
    {
        if ($request->has('plansMappingForm')) {
            $type = $request->input('plansMappingForm');
            $keyMap = [
                'homeowner' => 'homeowner_product_prices',
                'advisor'   => 'advisor_product_prices',
            ];
            if (!isset($keyMap[$type])) {
                return redirect()->back()->with('error', 'Invalid plan mapping type.');
            }
            $settingsKey = $keyMap[$type];

            $request->validate([
                "settings.$settingsKey"   => 'required|array',
                "settings.$settingsKey.*" => 'nullable|array',
            ]);


            $productPrices = $request->input("settings.$settingsKey", []);

            $agency->settings()->updateOrCreate(
                ['key' => $settingsKey],
                ['value' => json_encode($productPrices)]
            );

            return redirect()->back()->with(
                'success',
                ucfirst($type) . ' plan mapping updated successfully.'
            );
        }
        $validated = $request->validate([
            'settings' => 'required|array',
            'settings.crm_client_id' => 'nullable|string',
            'settings.crm_client_secret' => 'nullable|string',
            'settings.stripe_secret_key' => 'nullable|string',
            'settings.stripe_publishable_key' => 'nullable|string',
            'settings.company_cam_access_token' => 'nullable|string',
            'settings.homeowner_clone_location_id' => 'nullable|string',
            'settings.homeowner_clone_user_id' => 'nullable|string',
            'settings.advisor_clone_location_id' => 'nullable|string',
            'settings.advisor_clone_user_id' => 'nullable|string',
            'settings.primary_location' => 'nullable|string',
            'settings.primary_calendar' => 'nullable|string',
        ]);
        foreach ($validated['settings'] as $key => $value) {
            $agency->settings()->updateOrCreate(
                ['key' => $key],
                ['value' => $value ?? '']
            );
        }

        return redirect()->route('admin.settings.index', $agency)
            ->with('success', 'Agency settings updated successfully.');
    }
    public function userMapping(Agency $agency)
    {
        $locationId = CRM::getDefault('primary_location', '', $agency);
        $crmLocations = [];
        $crmService = new CrmService($agency);
        $crmLocationsResponse = $crmService->getLocations();
        $crmProducts = $crmService->getProducts($locationId);
        if (!empty($crmLocationsResponse) && property_exists($crmLocationsResponse, 'locations')) {
            $crmLocations = $crmLocationsResponse->locations;
        }
        if (!empty($crmProducts) && property_exists($crmProducts, 'products')) {
            $crmProducts = $crmProducts->products;
        }
        $crmUsers = [];
        $settings = $agency->settings->pluck('value', 'key');

        $selectedMappings = [
            'homeowner_product_prices' => [],
            'advisor_product_prices'   => [],
        ];

        if (!empty($settings['homeowner_product_prices'])) {
            $selectedMappings['homeowner_product_prices'] =
                json_decode($settings['homeowner_product_prices'], true);
        }

        if (!empty($settings['advisor_product_prices'])) {
            $selectedMappings['advisor_product_prices'] =
                json_decode($settings['advisor_product_prices'], true);
        }
        return view('admin.settings.user-mapping', compact('agency', 'selectedMappings', 'settings', 'crmLocations', 'crmProducts', 'crmUsers'));
    }

    public function homeOwnerMenu(Agency $agency)
    {
        // Fetch all agency settings as key => value
        $settings = $agency->settings->pluck('value', 'key');

        // Prepare readable individual setting values
        $stormUrl      = $settings['storm_season_url'] ?? '';
        $stormMode     = $settings['storm_season_open_mode'] ?? 'redirect';

        $questionUrl   = $settings['questionnaire_url'] ?? '';
        $questionMode  = $settings['questionnaire_open_mode'] ?? 'redirect';

        return view('admin.settings.homeowner-menu.index', compact(
            'agency',
            'settings',
            'stormUrl',
            'stormMode',
            'questionUrl',
            'questionMode'
        ));
    }

    public function homeOwnerMenuUpdate(Request $request, Agency $agency)
    {
        $request->validate([
            'storm_season_url' => 'required|url',
            'storm_season_open_mode' => 'required|in:redirect,iframe,new_tab',

            'questionnaire_url' => 'required|url',
            'questionnaire_open_mode' => 'required|in:redirect,iframe,new_tab',
        ]);

        Setting::updateOrCreate(
            ['key' => 'storm_season_url', 'agency_id' => $agency->id],
            ['value' => $request->storm_season_url]
        );

        Setting::updateOrCreate(
            ['key' => 'storm_season_open_mode', 'agency_id' => $agency->id],
            ['value' => $request->storm_season_open_mode]
        );

        Setting::updateOrCreate(
            ['key' => 'questionnaire_url', 'agency_id' => $agency->id],
            ['value' => $request->questionnaire_url]
        );

        Setting::updateOrCreate(
            ['key' => 'questionnaire_open_mode', 'agency_id' => $agency->id],
            ['value' => $request->questionnaire_open_mode]
        );

        return back()->with('success', 'Settings Updated Successfully');
    }
    public function getUsersByLocation($locationId)
    {
        try {
            $agency = CRM::getAgency();
            $crmService = new CrmService($agency);
            $usersResponse = $crmService->getUsersByLocation($locationId);

            if (!empty($usersResponse) && property_exists($usersResponse, 'users')) {
                $usersResponse = $usersResponse->users;
            }

            return response()->json([
                'status' => 'success',
                'data' => $usersResponse,
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching CRM users: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to load users for this location',
            ], 500);
        }
    }
    public function getCalendarByLocation($locationId)
    {
        try {
            $agency = CRM::getAgency();
            $crmService = new CrmService($agency);
            $crmCalendars = $crmService->getCalendar($locationId);
            if (!empty($crmCalendars) && property_exists($crmCalendars, 'calendars')) {
                $crmCalendars = $crmCalendars->calendars;
            }
            return response()->json([
                'status' => 'success',
                'data' => $crmCalendars,
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching CRM Calendar: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to load users for this location',
            ], 500);
        }
    }
    public function getProductPrices($productId)
    {
        $agency = CRM::getAgency();
        $locationId = CRM::getDefault('primary_location', '', $agency);
        $crmService = new CrmService($agency);
        $prices = $crmService->getProductPrices($productId, $locationId);
        if (!empty($prices) && property_exists($prices, 'prices')) {
            $prices = $prices->prices;
        }
        if ($prices) {
            return response()->json([
                'status' => 'success',
                'data' => $prices,
            ]);
        }
        return response()->json([
            'status' => 'error',
            'message' => 'Failed to load product prices',
        ], 500);
    }
}
