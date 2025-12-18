<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Inspection;
use App\Models\PartnerJob;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PartnerJobController extends Controller
{
    /**
     * Show the form for creating a new job from an inspection.
     */
    public function create(Inspection $inspection)
    {
        if ($inspection->agency_id !== Auth::user()->agency_id) {
            abort(403);
        }

        // Get all users with the 'partner' role in the current agency
        $partners = User::where('agency_id', Auth::user()->agency_id)
            ->where('role', 'partner')
            ->get();

        return view('admin.jobs.create', compact('inspection', 'partners'));
    }

    /**
     * Store a newly created job in storage.
     */
    public function store(Request $request, Inspection $inspection)
    {
        if ($inspection->agency_id !== Auth::user()->agency_id) {
            abort(403);
        }

        $request->validate([
            'partner_id' => 'required|exists:users,id',
            'job_description' => 'required|string',
        ]);

        PartnerJob::create([
            'inspection_id' => $inspection->id,
            'agency_id' => Auth::user()->agency_id,
            'partner_id' => $request->partner_id,
            'job_description' => $request->job_description,
            'status' => 'assigned',
        ]);

        // TODO: Notify the selected partner about the new job.

        return redirect()->route('admin.inspections.index')->with('success', 'Job has been created and assigned to the partner.');
    }
}
