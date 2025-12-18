@extends('layouts.admin')

@section('content')
    <h2>Feature Settings</h2>
    @if ($message = Session::get('success'))
        <div class="alert alert-success">{{ $message }}</div>
    @endif

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
    <form action="{{ route('admin.settings.homeowner-menu.update', $agency) }}" method="POST">
        @csrf

        <h4>Storm Season Prep Kit</h4>
        <div class="mb-3">
            <label>Base URL</label>
            <input type="text" class="form-control" name="storm_season_url" value="{{ $stormUrl }}">
        </div>

        <div class="mb-3">
            <label>Open Mode</label>
            <select name="storm_season_open_mode" class="form-control">
                <option value="redirect" {{ $stormMode == 'redirect' ? 'selected' : '' }}>Redirect</option>
                <option value="iframe" {{ $stormMode == 'iframe' ? 'selected' : '' }}>iFrame</option>
                <option value="new_tab" {{ $stormMode == 'new_tab' ? 'selected' : '' }}>New Tab</option>
            </select>
        </div>

        <hr>

        <h4>Questionnaire</h4>
        <div class="mb-3">
            <label>Base URL</label>
            <input type="text" class="form-control" name="questionnaire_url" value="{{ $questionUrl }}">
        </div>

        <div class="mb-3">
            <label>Open Mode</label>
            <select name="questionnaire_open_mode" class="form-control">
                <option value="redirect" {{ $questionMode == 'redirect' ? 'selected' : '' }}>Redirect</option>
                <option value="iframe" {{ $questionMode == 'iframe' ? 'selected' : '' }}>iFrame</option>
                <option value="new_tab" {{ $questionMode == 'new_tab' ? 'selected' : '' }}>New Tab</option>
            </select>
        </div>

        <button class="btn btn-primary">Save Settings</button>
    </form>
@endsection
