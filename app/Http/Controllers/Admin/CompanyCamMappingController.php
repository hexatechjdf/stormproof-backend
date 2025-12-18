<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\CompanyCamService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CompanyCamMappingController extends Controller
{
    public function index()
    {
        $agency = Auth::user()->agency;
        $advisors = User::where('agency_id', $agency->id)->where('role', 'advisor')->get();

        // Get users from CompanyCam via our service
        $companyCamService = new CompanyCamService($agency);
        $companyCamUsers = $companyCamService->getUsers();
        return view('admin.mappings.companycam', compact('advisors', 'companyCamUsers'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'mappings' => 'required|array',
            'mappings.*' => 'nullable|string', // The value will be the CompanyCam user ID
        ]);

        foreach ($request->mappings as $advisorId => $companyCamUserId) {
            $advisor = User::find($advisorId);
            // Ensure the admin can only update users in their own agency
            if ($advisor && $advisor->agency_id === Auth::user()->agency_id) {
                $advisor->update(['companycam_user_id' => $companyCamUserId]);
            }
        }

        return redirect()->route('admin.mappings.companycam.index')->with('success', 'CompanyCam user mappings have been updated.');
    }
}
