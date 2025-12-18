@extends('layouts.homeowner')
@section('title', 'Open Projects')

@section('content')
<div class="container my-5">
    <h1 class="mb-4 fw-bold">Open Projects</h1>

    @if ($projects->count())
        <div class="row g-4">
            @foreach ($projects as $project)
                <div class="col-12 col-md-6 col-lg-4">
                    <div class="card shadow-sm rounded-4 p-4 h-100" style="background-color:#f8f9fa">
                        <h5 class="card-title mb-3">
                            Inspection #{{ $project->id }} - {{ $project->home->address_line1 ?? 'N/A' }}
                        </h5>

                        <p class="mb-2">
                            <span class="fw-semibold">Status:</span>
                            @php
                                $statusClasses = [
                                    'open' => 'badge bg-primary',
                                    'in_progress' => 'badge bg-warning text-dark',
                                    'completed' => 'badge bg-success',
                                    'canceled' => 'badge bg-danger',
                                ];
                                $statusClass = $statusClasses[$project->status] ?? 'badge bg-secondary';
                            @endphp
                            <span class="{{ $statusClass }}">
                                {{ ucfirst(str_replace('_', ' ', $project->status)) }}
                            </span>
                        </p>

                        <p class="mb-2"><span class="fw-semibold">Home:</span> {{ $project->home->nickname ?? 'Primary Residence' }}</p>

                        <p class="mb-2"><span class="fw-semibold">Start Date:</span> {{ $project->start_date?->format('M j, Y') ?? 'TBD' }}</p>

                        <p class="mb-4"><span class="fw-semibold">End Date:</span> {{ $project->end_date?->format('M j, Y') ?? 'TBD' }}</p>

                        <div class="d-flex justify-content-end">
                            <a href="{{ route('homeowner.projects.show', $project) }}" 
                               class="btn btn-primary btn-rounded" 
                               aria-label="View details for Inspection #{{ $project->id }}">
                               View Details
                            </a>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <div class="card border-0 shadow-sm text-center py-5">
            <div class="card-body">
                <h5 class="text-muted mb-3">No active projects at the moment.</h5>
                <p class="small text-muted">When a service partner is working on your home, it will appear here.</p>
            </div>
        </div>
    @endif
</div>
@endsection
