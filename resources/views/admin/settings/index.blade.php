@extends('layouts.admin')

@section('title', 'Settings for ' . $agency->name)

@section('content')
    @if ($message = Session::get('success'))
        <div class="alert alert-success">{{ $message }}</div>
    @endif

    <div class="row">
        <div class="col-lg-4">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">CRM Connection (OAuth)</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.settings.update', $agency->id) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="mb-3">
                            <label class="form-label">Redirect URI</label>
                            <input type="text" class="form-control bg-light"
                                value="{{ route('crm.oauth_callback', 'crm') }}" readonly>
                        </div>
                        <div class="mb-3">
                            <label for="crm_client_id" class="form-label">Client ID</label>
                            <input type="text" id="crm_client_id" name="settings[crm_client_id]" class="form-control"
                                value="{{ $settings['crm_client_id'] ?? '' }}">
                        </div>
                        <div class="mb-3">
                            <label for="crm_client_secret" class="form-label">Client Secret</label>
                            <input type="password" id="crm_client_secret" name="settings[crm_client_secret]"
                                class="form-control" value="{{ $settings['crm_client_secret'] ?? '' }}">
                        </div>
                        <button type="submit" class="btn btn-primary">Save Connection Details</button>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            {{-- NEW CRM CONNECTION CARD --}}
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">CompanyCam (OAuth)</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.settings.update', $agency->id) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="mb-3">
                            <label for="company_cam_access_token" class="form-label">Access Token</label>
                            <input type="text" id="company_cam_access_token" name="settings[company_cam_access_token]"
                                class="form-control" value="{{ $settings['company_cam_access_token'] ?? '' }}">
                        </div>
                        <button type="submit" class="btn btn-primary">Save Connection Details</button>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-lg-4 d-none">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Stripe (OAuth)</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label">Webhook URI</label>
                        <input type="text" class="form-control bg-light" value="{{ route('webhook.data', 'crm') }}"
                            readonly>
                    </div>
                    <form action="{{ route('admin.settings.update', $agency->id) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="mb-3">
                            <label for="stripe_secret_key" class="form-label">Stripe Secret key</label>
                            <input type="text" id="stripe_secret_key" name="settings[stripe_secret_key]"
                                class="form-control" value="{{ $settings['stripe_secret_key'] ?? '' }}">
                        </div>
                        <div class="mb-3">
                            <label for="stripe_publishable_key" class="form-label">Stripe Publishable key</label>
                            <input type="password" id="stripe_publishable_key" name="settings[stripe_publishable_key]"
                                class="form-control" value="{{ $settings['stripe_publishable_key'] ?? '' }}">
                        </div>
                        <button type="submit" class="btn btn-primary">Save Connection Details</button>
                    </form>
                </div>
            </div>
        </div>
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Service Connections (OAuth)</h5>
            </div>
            <div class="card-body">
                <p>Connect third-party services to enable their functionality for this agency.</p>
                <form action="{{ route('admin.settings.update', $agency->id) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Primary Location</label>
                            <select name="settings[primary_location]" class="form-select" required>
                                <option value="">Select Location</option>

                                @foreach ($crmLocations as $loc)
                                    <option value="{{ $loc->id }}"
                                        {{ \App\Helper\CRM::getDefault('primary_location', '', $agency) == $loc->id ? 'selected' : '' }}>
                                        {{ $loc->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Primary Calendar</label>
                            <select name="settings[primary_calendar]" class="form-select" required>
                                <option value="">Select Calendar</option>
                                @foreach ($crmCalendars as $cal)
                                    <option value="{{ $cal['id'] }}"
                                        {{ \App\Helper\CRM::getDefault('primary_calendar', '', $agency) == $cal['id'] ? 'selected' : '' }}>
                                        {{ $cal['name'] }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-success px-4">💾 Save Settings</button>
                </form>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Service Connections (OAuth)</h5>
            </div>
            <div class="card-body">
                <p>Connect third-party services to enable their functionality for this agency.</p>
                <table class="table table-bordered">
                    <thead class="table-light">
                        <tr>
                            <th>Service</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td class="align-middle"><strong>CRM Agency</strong></td>
                            <td class="align-middle">
                                @if ($company_id && $company_name)
                                    <span class="badge bg-success">Connected</span>
                                    <div class="mt-1">
                                        <small>
                                            <strong>ID:</strong> {{ $company_id }}<br>
                                            <strong>Name:</strong> {{ $company_name }}
                                        </small>
                                    </div>
                                @else
                                    <span class="badge bg-secondary">Not Connected</span>
                                @endif
                            </td>
                            <td>
                                <a href="{{ $connectUrl }}" class="btn btn-primary">
                                    {{ $company_id && $company_name ? '🔄 Reconnect' : '⚡ Connect' }}
                                </a>
                            </td>
                        </tr>

                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
@push('scripts')
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function() {

            function loadCalendarsByLocation(locationId, targetSelect, selectedUserId = null) {
                if (!locationId) {
                    $(targetSelect).html('<option value="">-- Select CRM Calendar --</option>');
                    return;
                }

                $(targetSelect).html('<option>Loading Calendars...</option>');

                $.ajax({
                    url: `/admin/crm/calendars/${locationId}`,
                    type: "GET",
                    success: function(response) {
                        if (response.status === 'success') {
                            let options = '<option value="">-- Select CRM Calendar --</option>';
                            if (response.data && response.data.length > 0) {
                                $.each(response.data, function(index, user) {
                                    const selected = (selectedUserId && selectedUserId == user
                                        .id) ? 'selected' : '';
                                    options +=
                                        `<option value="${user.id}" ${selected}>${user.name}</option>`;
                                });
                            } else {
                                options += '<option value="">No Calendars found</option>';
                            }
                            $(targetSelect).html(options);
                        } else {
                            $(targetSelect).html('<option value="">Failed to load Calendars</option>');
                        }
                    },
                    error: function() {
                        $(targetSelect).html('<option value="">Error fetching Calendars</option>');
                    }
                });
            }

            // --- Handle Homeowner ---
            const primaryLocationSelect = $('select[name="settings[primary_location]"]');
            const primaryCalendarSelect = $('select[name="settings[primary_calendar]"]');
            const primaryLocationId = primaryLocationSelect.val();
            const primaryCalendarId = "{{ $settings['primary_calendar'] ?? '' }}";

            if (primaryLocationId) {
                loadCalendarsByLocation(primaryLocationId, primaryCalendarSelect, primaryCalendarId);
            }

            primaryLocationSelect.on('change', function() {
                loadCalendarsByLocation($(this).val(), primaryCalendarSelect);
            });
        });
    </script>
@endpush
