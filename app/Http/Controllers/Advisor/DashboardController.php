<?php

namespace App\Http\Controllers\Advisor;

use App\Http\Controllers\Controller;
use App\Mail\InspectionScheduledHomeownerNotification;
use App\Mail\JobClaimedAdminNotification;
use App\Models\Inspection;
use App\Models\Project;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use App\Services\CompanyCamService;
use Yajra\DataTables\Facades\DataTables;

class DashboardController extends Controller
{
    public function index()
    {
        $advisorId = Auth::id();
        $opportunities = Inspection::where('status', 'broadcasted')
            ->whereHas('broadcasts', function ($query) use ($advisorId) {
                $query->where('advisor_id', $advisorId)->where('status', 'offered');
            })
            ->with('homeowner')
            ->get();

        $myInspections = Inspection::where('assigned_advisor_id', $advisorId)
            ->with('homeowner')
            ->latest()
            ->get();

        return view('advisor.dashboard', compact('opportunities', 'myInspections'));
    }
    public function opportunitiesData(Request $request)
    {
        $opportunities = Inspection::with(['home', 'homeowner'])
            ->whereNull('assigned_advisor_id')
            ->where('home_id', '>', 0);

        return DataTables::eloquent($opportunities)
            ->addColumn('city', function ($inspection) {
                return $inspection->home->city ?? '-';
            })
            ->addColumn('action', function ($inspection) {
                return view('advisor.partials.opportunity-actions',  ['inspection' => $inspection])->render();
            })
            ->rawColumns(['action'])
            ->make(true);
    }

    public function assignedData(Request $request)
    {
        $myInspections = Inspection::with(['home', 'homeowner'])
            ->where('assigned_advisor_id', auth()->id())->where('home_id', '>', 0);;

        return DataTables::of($myInspections)
            ->addColumn('status', function ($inspection) {
                return '<span class="badge bg-primary">' . ucwords($inspection->status) . '</span>';
            })
            ->addColumn('action', function ($inspection) {
                return '<a href="' . route('advisor.inspections.show', $inspection->id ?? -1) . '" class="btn btn-info btn-sm">Manage</a>';
            })
            ->rawColumns(['status', 'action'])
            ->make(true);
    }
    public function claim(Inspection $inspection)
    {
        $advisorId = Auth::id();
        DB::beginTransaction();

        try {
            $inspection = Inspection::where('id', $inspection->id)->lockForUpdate()->first();

            if ($inspection->status !== 'broadcasted') {
                DB::rollBack();
                return redirect()->route('advisor.dashboard')->with('error', 'Sorry, this job is no longer available.');
            }
            $wasOffered = $inspection->broadcasts()->where('advisor_id', $advisorId)->exists();
            if (!$wasOffered) {
                DB::rollBack();
                abort(403, 'You were not offered this job.');
            }
            $inspection->assigned_advisor_id = $advisorId;
            $inspection->status = 'scheduled'; // Or 'pending_homeowner_confirmation'
            $inspection->save();

            $agency = $inspection->agency;
            $companyCamService = new CompanyCamService($agency);
            if ($companyCamService->isConfigured()) {
                $advisor = Auth::user();
                $projectName = "Inspection #{$inspection->id} - {$inspection->home->address_line1}";
                $projectId = $companyCamService->createProject($projectName, $inspection->home->address_line1, $advisor->companycam_user_id);

                if ($projectId) {
                    $project = new Project();
                    $project->home_id = $inspection->home_id;
                    $project->companycam_project_id = $projectId;
                    $project->title = $projectName;
                    $project->description = 'Project created for inspection.';
                    $project->status = 'open';
                    $project->save();

                    $inspection->companycam_project_id = $projectId;
                    $inspection->project_id = $project->id;
                    $inspection->save();
                } else {
                    // Log::warning("Failed to create CompanyCam project for Inspection ID: {$inspection->id}");
                }
            }


            $advisor = Auth::user();
            $homeowner = $inspection->homeowner;

            $admins = User::where('agency_id', $inspection->agency_id)->where('role', 'admin')->get();

            foreach ($admins as $admin) {
                Mail::to($admin->email)->send(new JobClaimedAdminNotification($inspection, $advisor));
            }

            Mail::to($homeowner->email)->send(new InspectionScheduledHomeownerNotification($inspection, $advisor));
            $inspection->broadcasts()->delete();
            DB::commit();
            return redirect()->route('advisor.dashboard')->with('success', 'Job claimed successfully! It has been added to your assignments.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('advisor.dashboard')->with('error', 'An error occurred while claiming the job. Please try again.' . $e->getMessage());
        }
    }
}
