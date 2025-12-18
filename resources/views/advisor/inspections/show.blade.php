@extends('layouts.advisor')
@section('title', 'Manage Inspection #' . $inspection->id)
@section('content')
    <h1>Manage Inspection #{{ $inspection->id }}</h1>
    <a href="{{ route('advisor.dashboard') }}" class="btn btn-secondary mb-3">Back to Dashboard</a>

    <div class="card">
        <div class="card-header">Details & Status Update</div>
        <div class="card-body">
            <p><strong>Homeowner:</strong> {{ $inspection->homeowner->name }}</p>
            <p><strong>Address:</strong> {{ $inspection->home->address_line1 }}</p>
            <p><strong>CompanyCam Project ID:</strong> {{ $inspection->companycam_project_id ?? 'Not available' }}</p>
            @if($inspection->companycam_project_id)
                <a href="https://app.companycam.com/projects/{{ $inspection->companycam_project_id }}" target="_blank" class="btn btn-primary mb-3">Open in CompanyCam</a>
            @endif
            <hr>
            <form action="{{ route('advisor.inspections.update', $inspection->id ) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="mb-3">
                    <label for="advisor_notes" class="form-label">Your Notes</label>
                    <textarea name="advisor_notes" id="advisor_notes" class="form-control" rows="4">{{ $inspection->advisor_notes }}</textarea>
                </div>
                <div class="mb-3">
                    <label for="status" class="form-label">Update Status</label>
                    <select name="status" class="form-select">
                        <option value="scheduled" {{ $inspection->status == 'scheduled' ? 'selected' : '' }}>Scheduled</option>
                        <option value="in_progress" {{ $inspection->status == 'in_progress' ? 'selected' : '' }}>In Progress</option>
                        <option value="ready_for_review" {{ $inspection->status == 'ready_for_review' ? 'selected' : '' }}>Finished - Ready for Review</option>
                    </select>
                </div>
                <button type="submit" class="btn btn-success">Save Changes</button>
            </form>
        </div>
    </div>
@endsection
