@extends('layouts.homeowner')
@section('title', 'My Inspections')

@section('content')

    <div class="container my-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="h4">{{ $project->title }}</h1>
            <a href="{{ route('homeowner.projects.index') }}" class="btn btn-secondary">Back to Projects</a>
        </div>

        <!-- Project Details -->
        <div class="card mb-4 shadow-sm" style="background-color:#f8f9fa">
            <div class="card-body">
                <p class="mb-2"><strong>Status:</strong>
                    @php
                        $statusClass = match ($project->status) {
                            'open' => 'badge bg-secondary',
                            'in_progress' => 'badge bg-warning',
                            'completed' => 'badge bg-success',
                            'canceled' => 'badge bg-danger',
                            default => 'badge bg-secondary',
                        };
                    @endphp
                    <span class="{{ $statusClass }}">{{ ucfirst(str_replace('_', ' ', $project->status)) }}</span>
                </p>
                <p class="mb-2"><strong>Home:</strong> {{ $project->home->nickname ?? $project->home->address_line1 }}</p>
                <p class="mb-2"><strong>Start Date:</strong> {{ $project->start_date?->format('M j, Y') ?? 'TBD' }}</p>
                <p class="mb-2"><strong>End Date:</strong> {{ $project->end_date?->format('M j, Y') ?? 'TBD' }}</p>
                @if ($project->description)
                    <p class="mb-2"><strong>Description:</strong> {{ $project->description }}</p>
                @endif
            </div>
        </div>

        <!-- Inspections -->
        <h5 class="mb-3">Inspections</h5>
        @if ($project->inspections->count())
            <div class="row g-3 mb-4">
                @foreach ($project->inspections as $inspection)
                    <div class="col-12 col-md-6">
                        <div class="card shadow-sm h-100" style="background-color:#f8f9fa">
                            <div class="card-body">
                                <p class="mb-1"><strong>Type:</strong>
                                    {{ ucfirst(str_replace('_', ' ', $inspection->type)) }}</p>
                                <p class="mb-1"><strong>Date:</strong>
                                    {{ $inspection->inspection_date?->format('M j, Y') ?? 'TBD' }}</p>
                                <p class="mb-1"><strong>Notes:</strong>
                                    {{ Str::limit($inspection->notes, 100) ?? 'No notes' }}</p>
                                <p class="mb-1"><strong>Photos Count:</strong> {{ $inspection->photos_count }}</p>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="alert alert-info">No inspections recorded yet.</div>
        @endif

        <!-- Photo Reports -->
        <h5 class="mb-3">Photo Reports</h5>
        @if ($project->photoReports->count())
            <div class="row g-3">
                @foreach ($project->photoReports as $report)
                    <div class="col-12 col-md-6 col-lg-4">
                        <div class="card h-100 shadow-sm" style="background-color:#f8f9fa">
                            <div class="card-body d-flex flex-column">
                                <h6 class="card-title">{{ $report->title ?? 'Untitled' }}</h6>
                                <p class="text-muted mb-1">Type: {{ ucfirst(str_replace('_', ' ', $report->type)) }}</p>
                                <p class="text-muted mb-2">Uploaded: {{ $report->created_at->format('M j, Y') }}</p>
                                <div class="mt-auto d-flex gap-2">
                                    <a href="{{ route('homeowner.photo-reports.show', $report) }}"
                                        class="btn btn-sm btn-outline-primary">View</a>
                                    <form action="{{ route('homeowner.photo-reports.download', $report) }}" method="POST">
                                        @csrf
                                        <button class="btn btn-sm btn-outline-secondary">Download</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="alert alert-info">No photo reports uploaded for this project yet.</div>
        @endif
    </div>
@endsection
