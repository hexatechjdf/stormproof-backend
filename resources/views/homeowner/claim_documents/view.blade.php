@extends('layouts.homeowner')
@section('title', 'View Document')

@section('content')
<div class="container my-4">

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h4">{{ $claimDocument->title }}</h1>
        <a href="{{ route('homeowner.claim-documents.index') }}" class="btn btn-secondary btn-sm">
            Back
        </a>
    </div>

    <div class="card shadow-sm">
        <div class="card-body">

            <p><strong>Document Type:</strong> {{ ucfirst(str_replace('_', ' ', $claimDocument->doc_type)) }}</p>
            <p><strong>Uploaded On:</strong> {{ $claimDocument->created_at->format('M j, Y') }}</p>
            <p><strong>Date of Document:</strong> {{ $claimDocument->date_of_document ? $claimDocument->date_of_document->format('M j, Y') : '—' }}</p>

            @if ($claimDocument->notes)
                <p><strong>Notes:</strong><br>{{ $claimDocument->notes }}</p>
            @endif

            <hr>

            <div class="mt-3">
                <h5>Document Preview:</h5>

                @php
                    $ext = strtolower(pathinfo($claimDocument->file_path, PATHINFO_EXTENSION));
                @endphp

                {{-- PDF Preview --}}
                @if ($ext === 'pdf')
                    <iframe src="{{ asset('storage/' . $claimDocument->file_path) }}" width="100%" height="600px"></iframe>

                {{-- Image Preview --}}
                @elseif (in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp']))
                    <img src="{{ asset('storage/' . $claimDocument->file_path) }}" class="img-fluid border rounded" alt="Document">

                {{-- Other Files --}}
                @else
                    <p class="text-muted">Preview not available for this file type.</p>
                @endif
            </div>

            <div class="mt-4">
                <a href="{{ route('homeowner.claim-documents.download', $claimDocument) }}" class="btn btn-primary">
                    Download Document
                </a>
            </div>

        </div>
    </div>
</div>
@endsection
