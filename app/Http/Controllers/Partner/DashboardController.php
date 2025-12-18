<?php
namespace App\Http\Controllers\Partner;

use App\Http\Controllers\Controller;
use App\Models\PartnerJob;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index( )
    {
        $partnerId = Auth::id();

        // Fetch all jobs assigned to this partner, eager-loading inspection and homeowner details
        $jobs = PartnerJob::where('partner_id', $partnerId)
                          ->with('inspection.homeowner')
                          ->latest()
                          ->get();

        return view('partner.dashboard', compact('jobs'));
    }
}
