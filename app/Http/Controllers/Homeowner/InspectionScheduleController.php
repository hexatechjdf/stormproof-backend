<?php

namespace App\Http\Controllers\Homeowner;

use App\Helper\CRM;
use App\Http\Controllers\Controller;
use App\Models\Agency;
use App\Models\Home;
use App\Models\Inspection;
use App\Services\CrmService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class InspectionScheduleController extends Controller
{
    public function create($inspection = null)
    {
        $user =  Auth::user();
        $inspection = Inspection::where('id', $inspection)->first();
        $agency = $user->agency;
        $primary_calendar = CRM::getDefault('primary_calendar', '', $agency);
        return view('homeowner.inspections.schedule', compact('inspection', 'primary_calendar'));
    }

    /**
     * Store the preferred dates for the inspection.
     */
    public function store(Request $request, $id = -1)
    {
        if ($id == -1) {
            $inspection = new Inspection();
            $inspection->trigger_type = $request->inspection_title;
            $inspection->homeowner_id = auth()->id();
            $inspection->agency_id = auth()->user()->agency_id;
            $inspection->status = 'pending_schedule';
            $inspection->home_id = Home::where('user_id', auth()->id())->first();
            $inspection->save();
        } else {
            $inspection = Inspection::where('id', $id)->first();
        }
        $request->validate([
            'preferred_dates' => 'required|array|min:1|max:3', // Require 1 to 3 dates
            'preferred_dates.*' => 'required|date|after:today',
        ]);
        $inspection->schedules()->delete();
        $user = Auth::user();
        $agency = $user->agency;
        $primary_location = CRM::getDefault('primary_location', '', $agency);
        foreach ($request->preferred_dates as $date) {
            $crmService = new CrmService($agency);
            $appointmentPayload = [
                'title' => 'Homeowner Availability Slot',
                'appointmentStatus' => 'new',
                'description' => "Contact Number: " . ($validated['contact_number'] ?? 'N/A'),
                'calendarId' => CRM::getDefault('primary_calendar', '', $agency),
                'locationId' => CRM::getDefault('primary_location', '', $agency),
                'contactId' => $user->crm_contact_id,
                'startTime' => Carbon::parse($date)->utc()->toIso8601String(),
            ];
            try {
                $newusersResponse = $crmService->createAppointment($appointmentPayload, $primary_location);
            } catch (\Exception $e) {
                // Log the error or handle it as needed
                Log::error('Failed to create CRM appointment: ' . $e->getMessage());
            }
            $inspection->schedules()->create([
                'preferred_date' => Carbon::parse($date),
            ]);
        }

        // Update the inspection status
        $inspection->update(['status' => 'pending_approval']);

        // TODO: Notify the admin that an inspection is ready for approval.

        return redirect()->route('homeowner.dashboard')->with('success', 'Your preferred dates have been submitted for review.');
    }
}
