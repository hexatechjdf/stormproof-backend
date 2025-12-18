<?php

namespace App\Http\Controllers\Homeowner;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use DataTables;

class DashboardController extends Controller
{
    public function index()
    {
        // Get all inspections for the logged-in homeowner, newest first
        $home = auth()->user()->homes()->first();
        $photoReportsCount = $home->photoReports()->count();
        $claimDocsCount = $home->claimDocuments()->count();
        $openProjectsCount = $home->projects()->whereIn('status', ['open', 'in_progress'])->count();
        $recentProjects = $home->projects()->latest()->take(5)->get();

        return view('homeowner.dashboard', compact('photoReportsCount', 'claimDocsCount', 'openProjectsCount', 'recentProjects'));
    }
    public function inspections()
    {

        return view('homeowner.inspections.index');
    }
    public function inspectionData()
    {
        $inspections = Auth::user()->homeownerInspections()->with('schedules')->latest();
        return DataTables::of($inspections)
            ->addColumn('trigger_type', function ($row) {
                return ucwords(str_replace('_', ' ', $row->trigger_type));
            })
            ->addColumn('property_address', function ($row) {
                return $row->home->address_line1;
            })
            ->addColumn('status', function ($row) {
                $statusClass = match ($row->status) {
                    'scheduled' => 'status-scheduled',
                    'pending_schedule' => 'status-pending_schedule',
                    'completed' => 'status-completed',
                    default => 'status-default',
                };
                return '<span class="status-badge ' . $statusClass . '">' . ucfirst(str_replace('_', ' ', $row->status)) . '</span>';
            })
            ->addColumn('action', function ($row) {
                if ($row->status === 'pending_schedule') {
                    return '<a href="' . route("homeowner.inspections.schedule", $row->id) . '" class="btn btn-primary btn-action btn-sm">Schedule Now</a>';
                }
                return '<button class="btn btn-secondary btn-action btn-sm" disabled>View Details</button>';
            })
            ->rawColumns(['status', 'action'])
            ->make(true);
    }
}
