@extends('layouts.admin')

@section('title', 'Manage Users')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1>Manage Homeowners & Advisors</h1>
        {{-- <a href="" class="btn btn-primary">Add New Users</a> --}}
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
                        <th>Role</th>
                        <th>Created At</th>
                        <th width="280px">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($users as $user)
                        <tr>
                            <td>{{ $user->id }}</td>
                            <td>{{ $user->name }}</td>
                            <td><span
                                    class="badge bg-{{ $user->role == 'homeowner' ? 'success' : 'secondary' }}">{{ ucfirst($user->role) }}</span>
                            </td>
                            <td>{{ $user->created_at->format('Y-m-d') }}</td>
                            <td>

                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center">No users found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="mt-3">
        {!! $users->links() !!}
    </div>
@endsection
