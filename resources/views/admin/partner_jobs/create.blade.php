@extends('layouts.admin')
@section('title', 'Create Job for Inspection #' . $inspection->id)
@section('content')
    <h1>Assign Job to Partner</h1>
    <p>This inspection was marked as "Action Needed". Please describe the job and assign it to a partner.</p>

    <div class="card">
        <div class="card-body">
            <form action="{{ route('admin.jobs.store', $inspection->id) }}" method="POST">
                @csrf
                <div class="mb-3">
                    <label for="partner_id" class="form-label">Assign to Partner</label>
                    <select name="partner_id" id="partner_id" class="form-select" required>
                        <option value="">-- Select a Partner --</option>
                        @foreach($partners as $partner)
                            <option value="{{ $partner->id }}">{{ $partner->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-3">
                    <label for="job_description" class="form-label">Job Description</label>
                    <textarea name="job_description" id="job_description" class="form-control" rows="5" required placeholder="e.g., Repair damaged fence section on the west side of the property."></textarea>
                </div>
                <button type="submit" class="btn btn-primary">Assign Job</button>
            </form>
        </div>
    </div>
@endsection
