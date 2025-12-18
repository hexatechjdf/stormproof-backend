@extends('layouts.superadmin')

@section('title', 'Add New Agency')

@section('content')
    <h1>Add New Agency</h1>
    <a href="{{ route('superadmin.agencies.index') }}" class="btn btn-secondary mb-3">Back</a>

    @if ($errors->any())
        <div class="alert alert-danger">
            <strong>Whoops!</strong> There were some problems with your input.  
  

            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="card">
        <div class="card-body">
            <form action="{{ route('superadmin.agencies.store') }}" method="POST">
                @csrf
                <div class="mb-3">
                    <label for="name" class="form-label">Agency Name</label>
                    <input type="text" name="name" class="form-control" placeholder="e.g., Dallas Storm Experts" value="{{ old('name') }}">
                </div>
                <div class="mb-3">
                    <label for="status" class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="active" {{ old('status') == 'active' ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ old('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary">Submit</button>
            </form>
        </div>
    </div>
@endsection
