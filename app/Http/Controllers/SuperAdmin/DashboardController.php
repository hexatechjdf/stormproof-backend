<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Agency;

class DashboardController extends Controller
{
    public function index()
    {
        $totalAgencies = Agency::count();
        // We can add more stats here later
        // $totalUsers = User::count();

        return view('superadmin.dashboard', compact('totalAgencies'));
    }
}
