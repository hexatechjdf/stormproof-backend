@extends('layouts.admin')

@section('title', 'Photo Reports')

@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between mb-4">
        <h2 class="fw-bold">Inspection Photo & Document Reports</h2>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#uploadModal">
            <i class="bi bi-upload"></i> Upload Report
        </button>
    </div>

    <div class="row g-4">
        @forelse($reports as $report)
            <div class="col-md-4">
                <div class="card shadow-sm h-100">

                    <div class="bg-light d-flex align-items-center justify-content-center"
                         style="height:180px; overflow:hidden;">
                        @if ($report->thumbnail_path)
                            <img src="{{ Storage::disk('s3')->url($report->thumbnail_path) }}"
                                 class="img-fluid h-100">
                        @else
                            <i class="bi bi-file-earmark-text fs-1 text-secondary"></i>
                        @endif
                    </div>

                    <div class="card-body d-flex flex-column">
                        <h5 class="fw-semibold">{{ $report->title }}</h5>

                        <p class="text-muted small">
                            <strong>Inspection:</strong> {{ $report->inspection->title ?? '—' }}<br>
                            {{ ucfirst($report->type) }} • {{ $report->created_at->format('M j, Y') }}
                        </p>

                        <div class="mt-auto d-flex gap-2">
                            <a href="{{ route('admin.photo-report.show', $report) }}"
                               class="btn btn-sm btn-primary flex-fill">
                                View
                            </a>

                            <form method="POST" action="{{ route('admin.photo-report.destroy', $report) }}"
                                  onsubmit="return confirm('Delete this report?')">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-danger">
                                    Delete
                                </button>
                            </form>
                        </div>
                    </div>

                </div>
            </div>
        @empty
            <div class="col-12 text-center py-5">
                <h5 class="text-muted">No reports uploaded yet.</h5>
            </div>
        @endforelse
    </div>

    <div class="mt-4">
        {{ $reports->links() }}
    </div>
</div>

@include('admin.photo_reports.partials.upload_modal')

@endsection
