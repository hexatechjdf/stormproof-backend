@extends('layouts.admin')
@section('title', 'Claim Ready Documents')

@section('content')

    <div class="container my-4">

        {{-- Page Header --}}
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="h4">Claim Ready Documents</h1>

            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#uploadModal">
                Upload Document
            </button>
        </div>

        {{-- Document List --}}
        @if ($docs->count())
            <div class="row g-3">
                @foreach ($docs as $doc)
                    <div class="col-12 col-md-6 col-lg-4">
                        <div class="card h-100 shadow-sm">

                            <div class="card-body d-flex flex-column">
                                <h5 class="card-title">{{ $doc->title }}</h5>

                                <p class="text-muted mb-1">
                                    Type: {{ ucfirst(str_replace('_', ' ', $doc->doc_type)) }}
                                </p>

                                <p class="text-muted mb-2">
                                    Uploaded: {{ $doc->created_at->format('M j, Y') }}
                                </p>

                                <div class="mt-auto d-flex gap-2">
                                    <a href="{{ route('homeowner.claim-documents.download', $doc) }}"
                                        class="btn btn-sm btn-outline-primary">
                                        Download
                                    </a>

                                    {{-- Delete --}}
                                    <form action="{{ route('homeowner.claim-documents.destroy', $doc) }}" method="POST"
                                        onsubmit="return confirm('Are you sure you want to delete this document?');">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-sm btn-outline-danger">Delete</button>
                                    </form>
                                    <a href="{{ route('homeowner.claim-documents.show', $doc) }}"
                                        class="btn btn-sm btn-outline-secondary">
                                        View
                                    </a>
                                </div>
                            </div>

                        </div>
                    </div>
                @endforeach
            </div>

            {{-- Pagination --}}
            <div class="mt-4">
                {{ $docs->links() }}
            </div>
        @else
            <div class="card text-center py-5">
                <div class="card-body">
                    <p class="mb-2">No claim documents uploaded yet.</p>
                    <p class="text-muted small">
                        Use the Upload button to add policy documents, wind mitigation reports, or inspection files.
                    </p>
                </div>
            </div>
        @endif


        {{-- Upload Modal --}}
        @include('homeowner.claim_documents.partials.upload-claim-document')

    </div>

@endsection
