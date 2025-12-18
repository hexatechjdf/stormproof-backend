@extends('layouts.superadmin')

@section('title', 'Manage Agencies')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1>Manage Agencies</h1>
        <a href="{{ route('superadmin.agencies.create') }}" class="btn btn-primary">Add New Agency</a>
    </div>

    @if ($message = Session::get('success'))
        <div class="alert alert-success">{{ $message }}</div>
    @endif

    <div class="card">
        <div class="card-body">
            <table class="table table-bordered mb-0">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Status</th>
                        <th>Created At</th>
                        <th width="280px">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($agencies as $agency)
                        <tr>
                            <td>{{ $agency->id }}</td>
                            <td>{{ $agency->name }}</td>
                            <td><span class="badge bg-{{ $agency->status == 'active' ? 'success' : 'secondary' }}">{{ ucfirst($agency->status) }}</span></td>
                            <td>{{ $agency->created_at->format('Y-m-d') }}</td>
                            <td>
                                <form action="{{ route('superadmin.agencies.destroy',$agency->id) }}" method="POST">
                                   <a class="btn btn-sm btn-secondary" href="{{ route('superadmin.agencies.settings.index', $agency->id) }}">Settings</a>

                                    <a class="btn btn-sm btn-info" href="{{ route('superadmin.agencies.edit',$agency->id) }}">Edit</a>
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center">No agencies found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="mt-3">
        {!! $agencies->links() !!}
    </div>
@endsection
