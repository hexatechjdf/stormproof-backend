<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Agency;
use Illuminate\Http\Request;

class AgencyController extends Controller
{
    /**
     * Display a listing of the agencies.
     */
    public function index()
    {
        $agencies = Agency::latest()->paginate(10); // Get all agencies, newest first
        return view('superadmin.agencies.index', compact('agencies'));
    }

    /**
     * Show the form for creating a new agency.
     */
    public function create()
    {
        return view('superadmin.agencies.create');
    }

    /**
     * Store a newly created agency in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:agencies',
            'status' => 'required|in:active,inactive',
        ]);

        Agency::create($request->all());

        return redirect()->route('superadmin.agencies.index')
            ->with('success', 'Agency created successfully.');
    }

    /**
     * Display the specified agency. (We can use this for a details page later)
     */
    public function show(Agency $agency)
    {
        // For now, redirect to the edit page.
        return redirect()->route('superadmin.agencies.edit', $agency);
    }

    /**
     * Show the form for editing the specified agency.
     */
    public function edit(Agency $agency)
    {
        return view('superadmin.agencies.edit', compact('agency'));
    }

    /**
     * Update the specified agency in storage.
     */
    public function update(Request $request, Agency $agency)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:agencies,name,' . $agency->id,
            'status' => 'required|in:active,inactive',
        ]);

        $agency->update($request->all());

        return redirect()->route('superadmin.agencies.index')
            ->with('success', 'Agency updated successfully.');
    }

    /**
     * Remove the specified agency from storage.
     */
    public function destroy(Agency $agency)
    {
        $agency->delete();

        return redirect()->route('superadmin.agencies.index')
            ->with('success', 'Agency deleted successfully.');
    }
}
