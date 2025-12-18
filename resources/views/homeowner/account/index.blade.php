@extends('layouts.homeowner')
@section('title', 'Account Details')

@section('content')

    <div class="container py-5" style="max-width: 700px;">

        <h1 class="h3 mb-4 fw-bold text-primary">Account Details</h1>

        @if (session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif

        {{-- Error Notifications --}}
        @if ($errors->any())
            <div class="alert alert-danger">
                <strong>We found some issues:</strong>
                <ul class="mb-0 mt-2 small">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="card shadow-sm border-0 rounded-3">
            <div class="card-body p-4">

                <form action="{{ route('homeowner.account.update', auth()->user()->id) }}" method="POST" novalidate>
                    @csrf
                    @method('PUT')

                    {{-- Full Name --}}
                    <div class="mb-4">
                        <label for="name" class="form-label fw-semibold">Full Name <span
                                class="text-danger">*</span></label>
                        <input type="text" id="name" name="name" value="{{ old('name', auth()->user()->name) }}"
                            class="form-control @error('name') is-invalid @enderror" placeholder="Enter your full name"
                            required>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Email --}}
                    <div class="mb-4">
                        <label for="email" class="form-label fw-semibold">Email Address <span
                                class="text-danger">*</span></label>
                        <input type="email" id="email" name="email"
                            value="{{ old('email', auth()->user()->email) }}"
                            class="form-control @error('email') is-invalid @enderror" placeholder="you@example.com"
                            required>
                        @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <button type="submit" class="btn btn-primary w-100 py-2 fw-semibold">
                        Update Account
                    </button>

                </form>

            </div>
        </div>

    </div>

@endsection
