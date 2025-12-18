@extends('layouts.admin')
@section('title', 'Review Inspection #' . $inspection->id)
@section('content')
    <h1>Review & Finalize Inspection #{{ $inspection->id }}</h1>
    <a href="{{ route('admin.inspections.index') }}" class="btn btn-secondary mb-3">Back to Dispatch</a>

    <div class="row">
        {{-- Left Column: Inspection Info --}}
        <div class="col-md-7">
            <div class="card mb-3">
                <div class="card-header">Inspection Details</div>
                <div class="card-body">
                    <p><strong>Homeowner:</strong> {{ $inspection->homeowner->name }}</p>
                    <p><strong>Address:</strong> {{ $inspection->home->address_line1 }}</p>
                    <p><strong>Assigned Advisor:</strong> {{ $inspection->advisor->name ?? 'N/A' }}</p>
                    <hr>
                    <h5>Advisor's Notes:</h5>
                    <p class="p-2 bg-light rounded">{{ $inspection->advisor_notes ?: 'No notes provided.' }}</p>
                </div>
            </div>
            <div class="card">
                <div class="card-header">CompanyCam Data</div>
                <div class="card-body">
                    @if($inspection->companycam_project_id)
                        <p>All photos and reports for this inspection can be found in the CompanyCam project.</p>
                        <a href="https://app.companycam.com/projects/{{ $inspection->companycam_project_id }}" target="_blank" class="btn btn-primary">Open Project in CompanyCam</a>
                    @else
                        <p class="text-muted">No CompanyCam project is linked to this inspection.</p>
                    @endif
                    {{-- In the future, we can embed photos directly here using the CompanyCam API --}}
                </div>
            </div>
        </div>

        {{-- Right Column: Admin Actions --}}
        <div class="col-md-5">
            <div class="card">
                <div class="card-header">Admin Actions</div>
                <div class="card-body">
                    <p>After reviewing all materials, choose the outcome for this inspection.</p>
                    <form action="{{ route('admin.inspections.finalize', $inspection->id ) }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label for="admin_notes" class="form-label">Admin Notes (Internal)</label>
                            <textarea name="admin_notes" id="admin_notes" class="form-control" rows="4"></textarea>
                        </div>
                        <div class="d-grid gap-2">
                            <button type="submit" name="outcome" value="completed" class="btn btn-success">Mark as Completed (No Issues)</button>
                            <button type="submit" name="outcome" value="action_needed" class="btn btn-warning">Action Needed (Assign to Partner)</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
