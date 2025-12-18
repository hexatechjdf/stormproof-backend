<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct() {}

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable|\Illuminate\Http\RedirectResponse
     */
    public function index()
    {
        $user = Auth::user();
        if (!$user) {
            return redirect('/login')->with('error', 'Your account is not configured correctly.');
        }
        switch ($user->role) {
            case 'superadmin':
                return redirect()->route('superadmin.dashboard');
            case 'admin':
                return redirect()->route('admin.dashboard');
            case 'homeowner':
                return redirect()->route('homeowner.dashboard');
            case 'advisor':
                return redirect()->route('advisor.dashboard');
            case 'partner':
                return redirect()->route('partner.dashboard');
            default:
                // If role is not set or unknown, log them out as a security measure.
                Auth::logout();
                return redirect('/login')->with('error', 'Your account is not configured correctly.');
        }
    }
}
