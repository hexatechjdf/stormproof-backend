<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index( )
    {
        $agency = Auth::user()->agency;
        return view('admin.dashboard', compact('agency'));
    }
}
