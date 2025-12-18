@extends('layouts.homeowner')
@section('title', 'My Inspections')

@section('content')
    <div class="container my-4">
        <!-- Greeting & Quick Summary -->
        <div class="card mb-4">
            <div class="card-body d-flex flex-column flex-md-row justify-content-between align-items-center">
                <div>
                    <h1 class="h4">Welcome, {{ auth()->user()->name }}</h1>
                    <p class="text-muted mb-0">Here's a quick overview of your home and projects.</p>
                </div>
                <div class="mt-3 mt-md-0 d-flex gap-2 align-items-center">
                    <!-- Weather widget placeholder -->
                    <div class="bg-primary text-white rounded p-2">
                        Weather: <span id="weather">Loading...</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Stats Cards -->
        <div class="row mb-4">
            <div class="col-6 col-lg-3 mb-3">
                <div class="card text-center">
                    <div class="card-body">
                        <i class="fas fa-file-alt fa-2x text-secondary"></i>
                        <h5 class="card-title mt-2">{{ $photoReportsCount ?? 0 }}</h5>
                        <p class="card-text text-muted">Photo Reports</p>
                    </div>
                </div>
            </div>
            <div class="col-6 col-lg-3 mb-3">
                <div class="card text-center">
                    <div class="card-body">
                        <i class="fas fa-folder-open fa-2x text-secondary"></i>
                        <h5 class="card-title mt-2">{{ $claimDocsCount ?? 0 }}</h5>
                        <p class="card-text text-muted">Claim Documents</p>
                    </div>
                </div>
            </div>
            <div class="col-6 col-lg-3 mb-3">
                <div class="card text-center">
                    <div class="card-body">
                        <i class="fas fa-project-diagram fa-2x text-secondary"></i>
                        <h5 class="card-title mt-2">{{ $openProjectsCount ?? 0 }}</h5>
                        <p class="card-text text-muted">Open Projects</p>
                    </div>
                </div>
            </div>
            <div class="col-6 col-lg-3 mb-3">
                <div class="card text-center">
                    <div class="card-body">
                        <i class="fas fa-user-circle fa-2x text-secondary"></i>
                        <h5 class="card-title mt-2">{{ auth()->user()->name }}</h5>
                        <p class="card-text text-muted">Account</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Projects -->
        <div class="card mb-4">
            <div class="card-body">
                <h5 class="card-title mb-3">Recent Projects</h5>
                @if ($recentProjects->count())
                    <ul class="list-group list-group-flush">
                        @foreach ($recentProjects as $project)
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <div>
                                    <a href="{{ route('homeowner.projects.show', $project) }}"
                                        class="fw-medium text-primary text-decoration-none">
                                        {{ $project->title }}
                                    </a>
                                    <p class="text-muted small mb-0">{{ ucfirst($project->status) }} •
                                        {{ $project->created_at->format('M j, Y') }}</p>
                                </div>
                                <div class="text-muted">
                                    <i class="fas fa-arrow-right"></i>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                @else
                    <p class="text-muted small">You have no recent projects. Schedule a documentation session to get
                        started!</p>
                @endif
            </div>
        </div>

        <!-- Quick Links / Actions -->
        <div class="row">
            <div class="col-6 col-lg-3 mb-3">
                <a href="{{ route('homeowner.photo-reports.index') }}" class="card text-center text-decoration-none h-100">
                    <div class="card-body d-flex flex-column justify-content-center align-items-center">
                        <i class="fas fa-camera fa-2x text-secondary"></i>
                        <p class="mt-2 fw-medium text-dark">Photo Reports</p>
                    </div>
                </a>
            </div>
            <div class="col-6 col-lg-3 mb-3">
                <a href="{{ route('homeowner.claim-documents.index') }}" class="card text-center text-decoration-none h-100">
                    <div class="card-body d-flex flex-column justify-content-center align-items-center">
                        <i class="fas fa-file-alt fa-2x text-secondary"></i>
                        <p class="mt-2 fw-medium text-dark">Claim Documents</p>
                    </div>
                </a>
            </div>
            <div class="col-6 col-lg-3 mb-3">
                <a href="{{ route('homeowner.projects.index') }}" class="card text-center text-decoration-none h-100">
                    <div class="card-body d-flex flex-column justify-content-center align-items-center">
                        <i class="fas fa-project-diagram fa-2x text-secondary"></i>
                        <p class="mt-2 fw-medium text-dark">Projects</p>
                    </div>
                </a>
            </div>
            <div class="col-6 col-lg-3 mb-3">
                <a href="{{ route('homeowner.questionnaire.index') }}" class="card text-center text-decoration-none h-100">
                    <div class="card-body d-flex flex-column justify-content-center align-items-center">
                        <i class="fas fa-list-check fa-2x text-secondary"></i>
                        <p class="mt-2 fw-medium text-dark">Home Questionnaire</p>
                    </div>
                </a>
            </div>
        </div>
    </div>
@endsection
@push('scripts')
    <script>
        // Optional: fetch weather widget for user's location
        document.getElementById('weather').innerText = '72°F Sunny'; // placeholder
    </script>
@endpush
