@extends('layouts.partner')
@section('title', 'My Assigned Jobs')
@section('content')
    <h1>My Assigned Jobs</h1>
    <p>This is a list of all repair jobs assigned to you.</p>

    <div class="card">
        <div class="card-body">
            <table class="table mb-0">
                <thead>
                    <tr>
                        <th>Job Description</th>
                        <th>Homeowner Name</th>
                        <th>Property Address</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($jobs as $job)
                        <tr>
                            <td>{{ $job->job_description }}</td>
                            <td>{{ $job->inspection->homeowner->name }}</td>
                            <td>{{ $job->inspection->home->address_line1 }}</td>
                            <td>
                                <span class="badge bg-primary">{{ ucwords($job->status) }}</span>
                            </td>
                            <td>
                                <a href="{{ route('partner.jobs.show', $job->id) }}" class="btn btn-info btn-sm">Manage
                                    Job</a>

                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center">You have no assigned jobs.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
