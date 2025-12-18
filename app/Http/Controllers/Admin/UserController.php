<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function index()
    {
        $agencyId = Auth::user()->agency_id;
        $users = User::where('agency_id', $agencyId)
            ->where('id', '!=', Auth::id())
            ->whereIn('role', ['homeowner', 'advisor'])
            ->latest()
            ->paginate(10);
        return view('admin.users.index', compact('users'));
    }

    public function create()
    {
        return view('admin.users.create');
    }

    public function store(Request $request)
    {
        $agencyId = Auth::user()->agency_id;
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->where(function ($query) use ($agencyId) {
                return $query->where('agency_id', $agencyId);
            })],
            'password' => 'required|string|min:8|confirmed',
            'role' => ['required', Rule::in(['homeowner', 'advisor', 'partner'])], // Admins can only create these roles
            'crm_location_id' => 'nullable|string|max:255',
            'crm_user_id' => 'nullable|string|max:255',
        ]);

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
            'agency_id' => $agencyId,
            'crm_location_id' => $request->crm_location_id,
            'crm_user_id' => $request->crm_user_id,
        ]);

        return redirect()->route('admin.users.index')->with('success', 'User created successfully.');
    }

    // We will skip show, edit, update, destroy for now to keep it concise, but the logic would be very similar.
}
