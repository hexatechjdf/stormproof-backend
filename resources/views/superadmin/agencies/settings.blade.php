@extends('layouts.superadmin')

@section('title', 'Settings for ' . $agency->name)

@section('content')
    <h1>Settings for: <span class="text-primary">{{ $agency->name }}</span></h1>
    <a href="{{ route('superadmin.agencies.index') }}" class="btn btn-secondary mb-3">Back to Agencies</a>

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

    <div class="card">
        <div class="card-header">
            API Keys & Integrations
        </div>
        <div class="card-body">
            <form action="{{ route('superadmin.agencies.settings.update', $agency->id) }}" method="POST">
                @csrf
                @method('PUT')

                @foreach ($settingKeys as $key)
                    <div class="mb-3">
                        <label for="{{ $key }}" class="form-label">{{ ucwords(str_replace('_', ' ', $key)) }}</label>
                        <input type="text" 
                               name="settings[{{ $key }}]" 
                               id="{{ $key }}" 
                               class="form-control" 
                               value="{{ old('settings.'.$key, $settings[$key] ?? '') }}">
                    </div>
                @endforeach
                
                <button type="submit" class="btn btn-primary">Save Settings</button>
            </form>
        </div>
    </div>
@endsection
