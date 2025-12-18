@extends('layouts.admin')
@section('title', 'Inspection Dispatch')
@section('content')
    <h1>Inspection Dispatch Center</h1>

    {{-- We can use tabs for different statuses --}}
    <ul class="nav nav-tabs" id="inspectionTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="pending-approval-tab" data-bs-toggle="tab" data-bs-target="#pending-approval"
                type="button" role="tab">
                Pending Approval <span
                    class="badge bg-warning text-dark">{{ isset($inspections['pending_approval']) ? $inspections['pending_approval']->count() : 0 }}</span>
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="broadcasted-tab" data-bs-toggle="tab" data-bs-target="#broadcasted" type="button"
                role="tab">
                Broadcasted <span class="badge bg-info text-dark">
                    {{ isset($inspections['broadcasted']) ? $inspections['broadcasted']->count() : 0 }}
                </span>
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="review-tab" data-bs-toggle="tab" data-bs-target="#review" type="button"
                role="tab">
                Ready for Review <span class="badge bg-success">
                    {{ isset($inspections['ready_for_review']) ? $inspections['ready_for_review']->count() : 0 }}
                </span>
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="action-needed-tab" data-bs-toggle="tab" data-bs-target="#action-needed"
                type="button" role="tab">
                Action Needed <span class="badge bg-danger">
                    {{ isset($inspections['action_needed']) ? $inspections['action_needed']->count() : 0 }}
                </span>
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="completed-tab" data-bs-toggle="tab" data-bs-target="#completed" type="button"
                role="tab">
                Completed <span class="badge bg-success">
                    {{ isset($inspections['completed']) ? $inspections['completed']->count() : 0 }}
                </span>
            </button>
        </li>
    </ul>

    <div class="tab-content" id="inspectionTabsContent">
        <div class="tab-pane fade show active" id="pending-approval" role="tabpanel">
            <div class="card mt-3">
                <div class="card-header">Inspections Awaiting Advisor Assignment</div>
                <div class="card-body">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Homeowner</th>
                                <th>Address</th>
                                <th>Preferred Dates</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($inspections['pending_approval'] ?? [] as $inspection)
                                <tr>
                                    <td>{{ $inspection->homeowner->name }}</td>
                                    <td>{{ $inspection->home->address_line1 }}</td>
                                    <td>
                                        @foreach ($inspection->schedules as $schedule)
                                            <div>{{ $schedule->preferred_date->format('D, M j, Y @ g:i A') }}</div>
                                        @endforeach
                                    </td>
                                    <td>
                                        <a href="{{ route('admin.inspections.show', $inspection->id) }}"
                                            class="btn btn-primary btn-sm">View & Assign</a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center">No inspections are pending approval.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="tab-pane fade" id="broadcasted" role="tabpanel">
            <div class="card mt-3">
                <div class="card-header">Inspections Awaiting Advisor Claim</div>
                <div class="card-body">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Homeowner</th>
                                <th>Address</th>
                                <th>Broadcasted To</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($inspections['broadcasted'] ?? [] as $inspection)
                                <tr>
                                    <td>{{ $inspection->homeowner->name }}</td>
                                    <td>{{ $inspection->home->address_line1 }}</td>
                                    <td>{{ $inspection->broadcasts->count() }} Advisor(s)</td>
                                    <td>
                                        <a href="{{ route('admin.inspections.show', $inspection->id) }}"
                                            class="btn btn-secondary btn-sm">View Details</a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center">No inspections are currently broadcasted.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="tab-pane fade" id="review" role="tabpanel">
            <div class="card mt-3">
                <div class="card-header">Inspections Ready for Admin Review</div>
                <div class="card-body">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Homeowner</th>
                                <th>Address</th>
                                <th>Advisor</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($inspections['ready_for_review'] ?? [] as $inspection)
                                <tr>
                                    <td>{{ $inspection->homeowner->name }}</td>
                                    <td>{{ $inspection->home->address_line1 }}</td>
                                    <td>{{ $inspection->advisor->name ?? 'N/A' }}</td>
                                    <td>
                                        <a href="{{ route('admin.inspections.review', $inspection->id) }}"
                                            class="btn btn-primary btn-sm">Review & Finalize</a>

                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center">No inspections are ready for review.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
        <div class="tab-pane fade" id="action-needed" role="tabpanel">
            <div class="card mt-3">
                <div class="card-header">Inspections Requiring Partner Work</div>
                <div class="card-body">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Homeowner</th>
                                <th>Partner Job Status</th>
                                <th>Assigned Partner</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($inspections['action_needed'] as $inspection)
                                <tr>
                                    <td>{{ $inspection->homeowner->name }}</td>
                                    <td>
                                        @if ($inspection->partnerJob)
                                            <span
                                                class="badge bg-warning text-dark">{{ ucwords(str_replace('_', ' ', $inspection->partnerJob->status)) }}</span>
                                        @else
                                            <span class="badge bg-secondary">Job Not Created</span>
                                        @endif
                                    </td>
                                    <td>{{ $inspection->partnerJob->partner->name ?? 'N/A' }}</td>
                                    <td>
                                        @if (!$inspection->partnerJob)
                                            <a href="{{ route('admin.partner_jobs.create', $inspection->id) }}"
                                                class="btn btn-primary btn-sm">Create Job</a>
                                        @else
                                            {{-- Link to a future job detail page --}}
                                            <a href="#" class="btn btn-info btn-sm">View Job</a>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center">No inspections require action.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="tab-pane fade" id="completed" role="tabpanel">
            <div class="card mt-3">
                <div class="card-header">Completed Inspections</div>
                <div class="card-body">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Homeowner</th>
                                <th>Address</th>
                                <th>Advisor</th>
                                <th>Completion Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($inspections['completed'] as $inspection)
                                <tr>
                                    <td>{{ $inspection->homeowner->name }}</td>
                                    <td>{{ $inspection->home?->address_line1 }}</td>
                                    <td>{{ $inspection->advisor->name ?? 'N/A' }}</td>
                                    <td>{{ $inspection->updated_at->format('Y-m-d') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center">No inspections have been completed.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
