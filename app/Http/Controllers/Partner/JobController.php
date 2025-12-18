<?php

namespace App\Http\Controllers\Partner;

use App\Http\Controllers\Controller;
use App\Models\PartnerJob;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class JobController extends Controller
{
    /**
     * Display the specified job for management.
     */
    public function show(PartnerJob $job)
    {
        // Security check: ensure the job belongs to the logged-in partner
        if ($job->partner_id !== Auth::id()) {
            abort(403);
        }
        $job->load('inspection.homeowner');
        return view('partner.jobs.show', compact('job'));
    }

    /**
     * Update the specified job in storage.
     */
    public function update(Request $request, PartnerJob $job)
    {
        if ($job->partner_id !== Auth::id()) {
            abort(403);
        }

        $request->validate([
            'status' => 'required|in:scheduled,in_progress,completed',
            'invoice' => 'nullable|file|mimes:pdf,jpg,png|max:2048', // 2MB Max
            'invoice_amount' => 'nullable|numeric|min:0',
        ]);

        $job->status = $request->status;
        $job->invoice_amount = $request->invoice_amount;

        // Handle file upload
        if ($request->hasFile('invoice')) {
            // Delete old invoice if it exists
            if ($job->invoice_path) {
                Storage::disk('public')->delete($job->invoice_path);
            }
            // Store new invoice
            $path = $request->file('invoice')->store('invoices', 'public');
            $job->invoice_path = $path;
        }

        $job->save();

        // TODO: Notify admin when job is marked 'completed'.

        return redirect()->route('partner.dashboard')->with('success', 'Job updated successfully.');
    }
}
