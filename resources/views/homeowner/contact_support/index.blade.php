@extends('layouts.homeowner')
@section('title', 'Contact Customer Success')

@section('content')
<div class="container my-4">

    <h1 class="h4 mb-4">Contact Customer Success</h1>

    @if (session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    <div class="card shadow-sm">
        <div class="card-body">

            <form action="{{ route('homeowner.contact-support.store') }}" method="POST">
                @csrf

                <div class="mb-3">
                    <label class="form-label">Subject</label>
                    <input type="text" name="subject" class="form-control" required>
                    @error('subject') <small class="text-danger">{{ $message }}</small> @enderror
                </div>

                <div class="mb-3">
                    <label class="form-label">Message</label>
                    <textarea name="message" rows="5" class="form-control" required></textarea>
                    @error('message') <small class="text-danger">{{ $message }}</small> @enderror
                </div>

                <button type="submit" class="btn btn-success">
                    Send Message
                </button>
            </form>

        </div>
    </div>

</div>
@endsection
