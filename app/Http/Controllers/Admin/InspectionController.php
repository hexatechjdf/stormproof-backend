<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Inspection;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use App\Mail\NewJobOpportunity;

class InspectionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $agencyId = Auth::user()->agency_id;
        // Fetch all inspections for the agency, along with related homeowner and schedule info
        $inspections = Inspection::where('agency_id', $agencyId)
            ->with(['homeowner', 'schedules', 'advisor'])
            ->latest()
            ->get();
            // ->groupBy('status'); // Group by status for easy display in tabs/sections
             $statuses = [
            'pending_approval', 
            'broadcasted', 
            'scheduled', 
            'in_progress', 
            'ready_for_review', 
            'action_needed', 
            'completed'
        ];
        // Group the inspections by status
        $inspectionsByStatus = $inspections->groupBy('status');
        // Ensure every status key exists, even if empty
        $inspections = [];
        foreach ($statuses as $status) {
            $inspections[$status] = $inspectionsByStatus->get($status, collect());
        }

        return view('admin.inspections.index', compact('inspections'));
    }
    public function show(Inspection $inspection)
    {
        // Security check: ensure inspection belongs to the admin's agency
        if ($inspection->agency_id !== Auth::user()->agency_id) {
            abort(403);
        }

        // Find available advisors based on zip code (for now, we get all advisors in the agency)
        // In the future, we'll add zip code matching logic.
        $availableAdvisors = User::where('agency_id', Auth::user()->agency_id)
            ->where('role', 'advisor')
            ->get();

        return view('admin.inspections.show', compact('inspection', 'availableAdvisors'));
    }
    public function broadcast(Request $request, Inspection $inspection)
    {
        // Security check: ensure inspection belongs to the admin's agency
        if ($inspection->agency_id !== Auth::user()->agency_id) {
            abort(403);
        }

        // Validate that at least one advisor was selected
        $validated = $request->validate([
            'advisors' => 'required|array|min:1',
            'advisors.*' => 'exists:users,id', // Ensure all selected IDs are valid users
        ]);

        // Clear any previous broadcast offers for this inspection to start fresh
        $inspection->broadcasts()->delete();

        // Create a broadcast record for each selected advisor
        foreach ($validated['advisors'] as $advisorId) {
            $inspection->broadcasts()->create([
                'advisor_id' => $advisorId,
                'status' => 'offered',
            ]);
            // --- SEND EMAIL ---
            $advisor = User::find($advisorId);
            if ($advisor) {
                Mail::to($advisor->email)->send(new NewJobOpportunity($inspection));
            }
            // TODO: Send a "New Job Opportunity" notification to each advisor.
        }

        // Update the inspection status to show it has been offered
        $inspection->update(['status' => 'broadcasted']);

        return redirect()->route('admin.inspections.index')
            ->with('success', "Inspection #{$inspection->id} has been broadcasted to the selected advisors.");
    }
    /**
     * Show the form for reviewing and finalizing an inspection.
     */
    public function review(Inspection $inspection)
    {
        // Security check: ensure inspection belongs to the admin's agency
        if ($inspection->agency_id !== Auth::user()->agency_id) {
            abort(403);
        }

        // Eager load relationships for efficiency
        $inspection->load(['homeowner', 'advisor']);

        // In the future, we would also fetch photos/reports from CompanyCam API here.

        return view('admin.inspections.review', compact('inspection'));
    }
    /**
     * Process the final outcome of an inspection review.
     */
    public function finalize(Request $request, Inspection $inspection)
    {
        if ($inspection->agency_id !== Auth::user()->agency_id) {
            abort(403);
        }

        $request->validate([
            'outcome' => 'required|in:completed,action_needed',
            'admin_notes' => 'nullable|string',
        ]);

        $inspection->update([
            'status' => $request->outcome,
            'admin_notes' => $request->admin_notes,
        ]);

        if ($request->outcome === 'completed') {
            // TODO: Notify homeowner that their inspection is complete.
            return redirect()->route('admin.inspections.index')->with('success', 'Inspection has been marked as complete.');
        }

        if ($request->outcome === 'action_needed') {
            // TODO: Redirect to a new "Create Partner Job" page, pre-filled with inspection data.
            return redirect()->route('partner_jobs.create', ['inspection' => $inspection->id]);
        }

        return redirect()->route('admin.inspections.index');
    }
}
