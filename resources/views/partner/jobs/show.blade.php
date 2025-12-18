@extends('layouts.partner')
@section('title', 'Manage Job')
@section('content')
    <h1>Manage Job</h1>
    <a href="{{ route('partner.dashboard') }}" class="btn btn-secondary mb-3">Back to Dashboard</a>

    <div class="row">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">Job & Homeowner Details</div>
                <div class="card-body">
                    <h5>Job Description</h5>
                    <p>{{ $job->job_description }}</p>
                    <hr>
                    <p><strong>Homeowner:</strong> {{ $job->inspection->homeowner->name }}</p>
                    <p><strong>Address:</strong> {{ $job->inspection->home->address_line1 }}</p>
                    {{-- We can add homeowner contact info here if needed --}}
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">Update Status & Invoice</div>
                <div class="card-body">
                    <form action="{{ route('partner.jobs.update', $job->id) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')
                        <div class="mb-3">
                            <label for="status" class="form-label">Job Status</label>
                            <select name="status" id="status" class="form-select">
                                <option value="assigned" {{ $job->status == 'assigned' ? 'selected' : '' }}>Assigned</option>
                                <option value="scheduled" {{ $job->status == 'scheduled' ? 'selected' : '' }}>Scheduled</option>
                                <option value="in_progress" {{ $job->status == 'in_progress' ? 'selected' : '' }}>In Progress</option>
                                <option value="completed" {{ $job->status == 'completed' ? 'selected' : '' }}>Completed</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="invoice_amount" class="form-label">Invoice Amount ($)</label>
                            <input type="number" step="0.01" name="invoice_amount" class="form-control" value="{{ $job->invoice_amount }}">
                        </div>
                        <div class="mb-3">
                            <label for="invoice" class="form-label">Upload Invoice (PDF, JPG, PNG)</label>
                            <input type="file" name="invoice" class="form-control">
                            @if($job->invoice_path)
                                <div class="mt-2">Current Invoice: <a href="{{ Storage::url($job->invoice_path) }}" target="_blank">View</a></div>
                            @endif
                        </div>
                        <button type="submit" class="btn btn-primary">Update Job</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
