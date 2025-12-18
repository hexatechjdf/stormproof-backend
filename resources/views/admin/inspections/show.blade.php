@extends('layouts.admin')
@section('title', 'Assign Inspection')
@section('content')
    <h1>Assign Inspection #{{ $inspection->id }}</h1>
    <a href="{{ route('admin.inspections.index') }}" class="btn btn-secondary mb-3">Back to Dispatch</a>

    <div class="row">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">Inspection Details</div>
                <div class="card-body">
                    <p><strong>Homeowner:</strong> {{ $inspection->homeowner->name }} ({{ $inspection->homeowner->email }})
                    </p>
                    <p><strong>Address:</strong> {{ $inspection->home->address_line1 }}</p>
                    <p><strong>Zip Code:</strong> {{ $inspection->home->postal_code }}</p>
                    <p><strong>Status:</strong> {{ ucwords(str_replace('_', ' ', $inspection->status)) }}</p>
                    <hr>
                    <h5>Homeowner's Preferred Dates:</h5>
                    <ul>
                        @foreach ($inspection->schedules as $schedule)
                            <li>{{ $schedule->preferred_date->format('F j, Y, g:i a') }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">Assign to Advisor</div>
                <div class="card-body">
                    <p>The following advisors are available in this agency. In the future, this list will be filtered by zip
                        code.</p>

                    {{-- This form will handle the broadcast logic in the next step --}}
                    <form action="{{ route('admin.inspections.broadcast', $inspection->id) }}" method="POST">

                        @csrf
                        <div class="mb-3">
                            <label class="form-label">Select Advisors to Notify:</label>
                            @foreach ($availableAdvisors as $advisor)
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="advisors[]"
                                        value="{{ $advisor->id }}" id="advisor_{{ $advisor->id }}" checked>
                                    <label class="form-check-label" for="advisor_{{ $advisor->id }}">
                                        {{ $advisor->name }}
                                    </label>
                                </div>
                            @endforeach
                        </div>
                        <button type="submit" class="btn btn-primary">Broadcast Job to Selected Advisors</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
