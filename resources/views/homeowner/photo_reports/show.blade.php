@extends('layouts.admin')

@section('title', $photoReport->title)

@section('content')
<div class="container py-4">

    <h2 class="fw-bold mb-3">{{ $photoReport->title }}</h2>

    <p class="text-muted">
        <strong>Inspection:</strong> {{ $photoReport->inspection->title ?? '—' }} <br>
        {{ ucfirst($photoReport->type) }} • {{ $photoReport->created_at->format('M j, Y') }}
    </p>

    <div class="card shadow-sm p-3">

        @if ($photoReport->isDocument())
            {{-- PDF / DOC Viewer --}}
            <iframe src="{{ $photoReport->url }}"
                    class="w-100"
                    style="height: 600px; border: none;">
            </iframe>
        @else
            {{-- Image Viewer --}}
            <img src="{{ $photoReport->url }}"
                 class="img-fluid rounded">
        @endif

    </div>

    <div class="mt-4">
        <a href="{{ route('homeowner.photo-reports.download', $photoReport) }}"
           class="btn btn-primary">
            Download
        </a>

        <a href="{{ route('homeowner.photo-reports.index') }}"
           class="btn btn-secondary">
            Back
        </a>
    </div>

</div>
@endsection
