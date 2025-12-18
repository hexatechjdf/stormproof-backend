<?php

namespace App\Http\Controllers\Homeowner;

use App\Http\Controllers\Controller;
use App\Models\Inspection;
use App\Models\InspectionAppointment;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Carbon\Carbon;

class InspectionScheduleController extends Controller
{
    /**
     * Show the inspection scheduling view
     */
    public function create(Inspection $inspection)
    {
        // Get user's timezone or detect from browser
        $userTimezone = auth()->user()->timezone ?? config('app.timezone');

        return view('homeowner.inspections.schedule', [
            'inspection' => $inspection,
            'userTimezone' => $userTimezone,
        ]);
    }

    /**
     * Store the inspection appointment request
     */
    public function store(Request $request, Inspection $inspection): JsonResponse
    {
        $validated = $request->validate([
            'preferred_dates' => 'required|array|min:1|max:3',
            'preferred_dates.*' => 'required|date_format:Y-m-d\TH:i|after:now',
            'inspection_type' => 'required|string|in:general,pest,mold,radon,termite,roof,foundation,electrical,plumbing',
            'contact_method' => 'required|string|in:phone,email,sms,whatsapp',
            'contact_number' => 'nullable|string|regex:/^[+]?[0-9\s\-\(\)]+$/',
            'contact_email' => 'nullable|email',
            'notes' => 'nullable|string|max:500',
            'access_instructions' => 'nullable|boolean',
            'access_details' => 'nullable|string|max:1000',
        ]);

        try {
            // Create appointment records for each preferred date
            $appointments = [];
            foreach ($validated['preferred_dates'] as $dateTime) {
                $appointment = InspectionAppointment::create([
                    'inspection_id' => $inspection->id,
                    'homeowner_id' => auth()->id(),
                    'preferred_datetime' => Carbon::createFromFormat('Y-m-d\TH:i', $dateTime),
                    'inspection_type' => $validated['inspection_type'],
                    'contact_method' => $validated['contact_method'],
                    'contact_number' => $validated['contact_number'] ?? null,
                    'contact_email' => $validated['contact_email'] ?? null,
                    'notes' => $validated['notes'] ?? null,
                    'access_instructions' => $validated['access_details'] ?? null,
                    'status' => 'pending', // pending, confirmed, completed, cancelled
                ]);

                $appointments[] = $appointment;
            }

            // Send notification to inspector
            // Notification::send($inspection->inspector, new AppointmentRequestNotification($appointments));

            return response()->json([
                'success' => true,
                'message' => 'Appointment request submitted successfully',
                'appointments' => $appointments,
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create appointment: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get appointments for the calendar (API endpoint)
     */
    public function getAppointments(Inspection $inspection): JsonResponse
    {
        $appointments = InspectionAppointment::where('inspection_id', $inspection->id)
            ->where('status', '!=', 'cancelled')
            ->get()
            ->map(function ($apt) {
                return [
                    'id' => $apt->id,
                    'title' => $apt->inspection_type . ' - ' . $apt->status,
                    'start_time' => $apt->preferred_datetime->toIso8601String(),
                    'end_time' => $apt->preferred_datetime->addHour()->toIso8601String(),
                    'status' => $apt->status,
                    'type' => $apt->inspection_type,
                    'contact_method' => $apt->contact_method,
                ];
            });

        return response()->json([
            'appointments' => $appointments,
        ]);
    }

    /**
     * Get available time slots (for future enhancement)
     */
    public function getAvailableSlots(Inspection $inspection, Request $request): JsonResponse
    {
        $date = $request->query('date');
        $timezone = $request->query('timezone', config('app.timezone'));

        if (!$date) {
            return response()->json(['error' => 'Date parameter required'], 400);
        }

        // Define available time slots
        $slots = [
            '08:00', '08:30', '09:00', '09:30', '10:00', '10:30',
            '11:00', '11:30', '13:00', '13:30', '14:00', '14:30',
            '15:00', '15:30', '16:00', '16:30', '17:00',
        ];

        // Get booked slots for the date
        $bookedSlots = InspectionAppointment::where('inspection_id', $inspection->id)
            ->whereDate('preferred_datetime', $date)
            ->where('status', '!=', 'cancelled')
            ->pluck('preferred_datetime')
            ->map(fn($dt) => $dt->format('H:i'))
            ->toArray();

        // Filter available slots
        $availableSlots = array_filter($slots, fn($slot) => !in_array($slot, $bookedSlots));

        return response()->json([
            'date' => $date,
            'available_slots' => array_values($availableSlots),
            'booked_slots' => $bookedSlots,
            'timezone' => $timezone,
        ]);
    }

    /**
     * Update appointment status (for inspector)
     */
    public function updateStatus(InspectionAppointment $appointment, Request $request): JsonResponse
    {
        $validated = $request->validate([
            'status' => 'required|in:pending,confirmed,completed,cancelled',
            'notes' => 'nullable|string|max:500',
        ]);

        try {
            $appointment->update([
                'status' => $validated['status'],
                'inspector_notes' => $validated['notes'] ?? null,
            ]);

            // Send notification to homeowner
            // Notification::send($appointment->homeowner, new AppointmentStatusNotification($appointment));

            return response()->json([
                'success' => true,
                'message' => 'Appointment status updated',
                'appointment' => $appointment,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update appointment: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Cancel appointment
     */
    public function cancel(InspectionAppointment $appointment): JsonResponse
    {
        try {
            $appointment->update(['status' => 'cancelled']);

            return response()->json([
                'success' => true,
                'message' => 'Appointment cancelled successfully',
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to cancel appointment: ' . $e->getMessage(),
            ], 500);
        }
    }
}