<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Agency;
use Illuminate\Http\Request;

class AgencySettingsController extends Controller
{
    /**
     * Display the settings form for a given agency.
     */
    public function index(Agency $agency)
    {
        // Eager load the settings to avoid multiple queries
        $agency->load('settings');

        // We can define the keys we expect to manage
        $settingKeys = [
            'stripe_api_key',
            'stripe_webhook_secret',
            'gohighlevel_api_key',
            'companycam_api_key',
        ];

        // Create a simple key-value map of existing settings for easy access in the view
        $settings = $agency->settings->pluck('value', 'key');

        return view('superadmin.agencies.settings', compact('agency', 'settings', 'settingKeys'));
    }

    /**
     * Update the settings for a given agency.
     */
    public function update(Request $request, Agency $agency)
    {
        $validated = $request->validate([
            'settings' => 'required|array',
            'settings.stripe_api_key' => 'nullable|string',
            'settings.stripe_webhook_secret' => 'nullable|string',
            'settings.gohighlevel_api_key' => 'nullable|string',
            'settings.companycam_api_key' => 'nullable|string',
        ]);

        foreach ($validated['settings'] as $key => $value) {
            // Use updateOrCreate to either create a new setting or update an existing one
            $agency->settings()->updateOrCreate(
                ['key' => $key], // Conditions to find the record
                ['value' => $value ?? ''] // Data to update or create with
            );
        }

        return redirect()->route('superadmin.agencies.settings.index', $agency)
                         ->with('success', 'Agency settings updated successfully.');
    }
}
