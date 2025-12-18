@extends('layouts.homeowner')
@section('title', 'My Inspection Reports')

@section('content')

<div class="container my-4">

    <h2 class="mb-4 fw-bold">My Inspection Reports</h2>

    <div class="row g-4">
        @forelse($reports as $report)
            <div class="col-12 col-md-6 col-lg-4">
                <div class="card border-0 shadow-sm h-100">

                    {{-- Thumbnail / File Icon --}}
                    <div class="card-img-top bg-light d-flex align-items-center justify-content-center"
                         style="height: 180px; overflow:hidden;">

                        @if ($report->thumbnail_path)
                            <img src="{{ Storage::disk('s3')->url($report->thumbnail_path) }}"
                                 alt="{{ $report->title }}"
                                 class="img-fluid h-100 w-auto">
                        @elseif(in_array($report->extension, ['pdf','doc','docx']))
                            {{-- File icon --}}
                            <div class="text-center">
                                <i class="bi bi-file-earmark-text" style="font-size: 3rem;"></i>
                                <p class="mt-2 text-muted">{{ strtoupper($report->extension) }} File</p>
                            </div>
                        @else
                            <span class="text-muted">No Preview Available</span>
                        @endif
                    </div>

                    {{-- Card Body --}}
                    <div class="card-body d-flex flex-column">

                        {{-- Report Title --}}
                        <h5 class="fw-semibold text-dark">
                            {{ $report->title ?? 'Untitled Report' }}
                        </h5>

                        {{-- Inspection Title --}}
                        <p class="text-muted small mb-1">
                            <strong>Inspection:</strong>
                            {{ $report->inspection->title ?? 'No Inspection Assigned' }}
                        </p>

                        {{-- Report Type + Date --}}
                        <p class="text-muted small">
                            {{ ucfirst($report->type) }} • {{ $report->created_at->format('M j, Y') }}
                        </p>

                        <div class="mt-auto d-flex gap-2">

                            {{-- View Button --}}
                            <a href="{{ route('homeowner.photo-reports.show', $report) }}"
                                class="btn btn-primary btn-sm flex-grow-1">
                                <i class="bi bi-eye"></i> View
                            </a>

                            {{-- Download Button --}}
                            <form action="{{ route('homeowner.photo-reports.download', $report) }}" method="POST"
                                  class="flex-grow-1">
                                @csrf
                                <button class="btn btn-outline-secondary btn-sm w-100">
                                    <i class="bi bi-download"></i> Download
                                </button>
                            </form>
                        </div>
                    </div>

                </div>
            </div>

        @empty
            <div class="col-12">
                <div class="alert alert-info text-center py-4">
                    <h5 class="mb-1">No Reports Yet</h5>
                    <p class="mb-0 small">Your inspection reports will appear here once uploaded.</p>
                </div>
            </div>
        @endforelse
    </div>

    {{-- Pagination --}}
    <div class="mt-4 d-flex justify-content-center">
        {{ $reports->links() }}
    </div>

</div>

@endsection
