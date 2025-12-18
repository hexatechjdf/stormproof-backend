@extends('layouts.superadmin')

@section('title', 'Edit Agency')

@section('content')
    <h1>Edit Agency</h1>
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
            <form action="{{ route('superadmin.agencies.update', $agency->id) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="mb-3">
                    <label for="name" class="form-label">Agency Name</label>
                    <input type="text" name="name" class="form-control" value="{{ $agency->name }}">
                </div>
                <div class="mb-3">
                    <label for="status" class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="active" {{ $agency->status == 'active' ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ $agency->status == 'inactive' ? 'selected' : '' }}>Inactive</option>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary">Update</button>
            </form>
        </div>
    </div>
@endsection
