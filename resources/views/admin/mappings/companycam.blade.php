@extends('layouts.admin')
@section('title', 'CompanyCam User Mapping')
@section('content')
    <h1>CompanyCam User Mapping</h1>
    <p>Match your platform advisors to their corresponding CompanyCam user accounts.</p>

    @if (empty($companyCamUsers))
        <div class="alert alert-warning">
            Could not fetch users from CompanyCam. Please ensure your API key is configured correctly in the Super Admin settings.
        </div>
    @else
        @if ($message = Session::get('success'))
            <div class="alert alert-success">{{ $message }}</div>
        @endif

        <form action="{{ route('admin.mappings.companycam.update') }}" method="POST">
            @csrf
            <div class="card">
                <div class="card-body">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Platform Advisor</th>
                                <th>CompanyCam User</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($advisors as $advisor)
                                <tr>
                                    <td>{{ $advisor->name }}</td>
                                    <td>
                                        <select name="mappings[{{ $advisor->id }}]" class="form-select">
                                            <option value="">-- Not Mapped --</option>
                                            @foreach ($companyCamUsers as $ccUser)
                                                <option value="{{ $ccUser['id'] }}" {{ $advisor->companycam_user_id == $ccUser['id'] ? 'selected' : '' }}>
                                                    {{ $ccUser['name'] }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            <button type="submit" class="btn btn-primary mt-3">Save Mappings</button>
        </form>
    @endif
@endsection
