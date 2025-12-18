<?php
namespace App\Http\Controllers\Advisor;

use App\Http\Controllers\Controller;
use App\Models\Inspection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class InspectionController extends Controller
{
    /**
     * Display the specified inspection for management.
     */
    public function show(Inspection $inspection)
    {
        // Security check: ensure this inspection is assigned to the logged-in advisor
        if ($inspection->assigned_advisor_id !== Auth::id()) {
            abort(403);
        }
        return view('advisor.inspections.show', compact('inspection'));
    }

    /**
     * Update the inspection status.
     */
    public function update(Request $request, Inspection $inspection)
    {
        if ($inspection->assigned_advisor_id !== Auth::id()) {
            abort(403);
        }

        $request->validate([
            'status' => 'required|in:in_progress,ready_for_review',
            'advisor_notes' => 'nullable|string',
        ]);

        $inspection->update([
            'status' => $request->status,
            'advisor_notes' => $request->advisor_notes,
        ]);

        // TODO: Notify admin when status is 'ready_for_review'

        return redirect()->route('advisor.dashboard')->with('success', 'Inspection status updated successfully.');
    }
}
