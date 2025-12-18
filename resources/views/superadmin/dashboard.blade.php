@extends('layouts.superadmin')

@section('title', 'Dashboard')

@section('content')
    <h1>Super Admin Dashboard</h1>
    <p>Welcome, {{ Auth::user()->name }}!</p>

    <div class="row">
        <div class="col-md-4">
            <div class="card text-white bg-primary mb-3">
                <div class="card-header">Total Agencies</div>
                <div class="card-body">
                    <h5 class="card-title" style="font-size: 2.5rem;">{{ $totalAgencies }}</h5>
                    <p class="card-text">Total number of agencies managed by the platform.</p>
                    <a href="{{ route('superadmin.agencies.index') }}" class="text-white">View Agencies &rarr;</a>
                </div>
            </div>
        </div>
        {{-- Add more stat cards here later --}}
    </div>
@endsection
