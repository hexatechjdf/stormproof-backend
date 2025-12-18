@extends('layouts.admin')

@section('title', 'Settings for ')

@section('content')
    @if ($message = Session::get('success'))
        <div class="alert alert-success">{{ $message }}</div>
    @endif
    <form action="{{ route('admin.settings.update', ['agency' => Auth::user()->agency_id]) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="row">

            <div class="col-lg-6">
                <div class="tab-content" id="settingsTabsContent">
                    <div class="tab-pane fade show active" id="mapping" role="tabpanel">
                        <div class="card mt-3">
                            <div class="card-header">Homeowner Creation Mapping</div>
                            <div class="card-body">
                                <p>When a new user is created in the CRM, if they are in the Location below, a `Homeowner`
                                    will be cloned in our system using the selected User as a template.</p>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">CRM Location</label>
                                        <select name="settings[homeowner_clone_location_id]" class="form-select">
                                            <option value="">-- Select CRM Location --</option>
                                            @foreach ($crmLocations as $location)
                                                <option value="{{ $location->id }}"
                                                    {{ ($settings['homeowner_clone_location_id'] ?? '') == $location->id ? 'selected' : '' }}>
                                                    {{ $location->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Clone from CRM User (Template)</label>
                                        <select name="settings[homeowner_clone_user_id]" class="form-select">
                                            <option value="">-- Select CRM User --</option>

                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="tab-pane fade show active" id="mapping" role="tabpanel">
                    <div class="card mt-3">
                        <div class="card-header">Advisor Creation Mapping</div>
                        <div class="card-body">
                            <p>When a new user is created in the CRM, if they are in the Location below, an `Advisor`
                                will be cloned in our system using the selected User as a template.</p>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">CRM Location</label>
                                    <select name="settings[advisor_clone_location_id]" class="form-select">
                                        <option value="">-- Select CRM Location --</option>
                                        @foreach ($crmLocations as $location)
                                            <option value="{{ $location->id }}"
                                                {{ ($settings['advisor_clone_location_id'] ?? '') == $location->id ? 'selected' : '' }}>
                                                {{ $location->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Clone from CRM User (Template)</label>
                                    <select name="settings[advisor_clone_user_id]" class="form-select">
                                        <option value="">-- Select CRM User --</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-6">
                    <button type="submit" class="btn btn-primary mt-4">Save All Settings</button>

                </div>
            </div>

        </div>
    </form>
@endsection
@push('scripts')
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function() {

            function loadUsersByLocation(locationId, targetSelect, selectedUserId = null) {
                if (!locationId) {
                    $(targetSelect).html('<option value="">-- Select CRM User --</option>');
                    return;
                }

                $(targetSelect).html('<option>Loading users...</option>');

                $.ajax({
                    url: `/admin/crm/users/${locationId}`,
                    type: "GET",
                    success: function(response) {
                        if (response.status === 'success') {
                            let options = '<option value="">-- Select CRM User --</option>';
                            if (response.data && response.data.length > 0) {
                                $.each(response.data, function(index, user) {
                                    const selected = (selectedUserId && selectedUserId == user
                                        .id) ? 'selected' : '';
                                    options +=
                                        `<option value="${user.id}" ${selected}>${user.name}</option>`;
                                });
                            } else {
                                options += '<option value="">No users found</option>';
                            }
                            $(targetSelect).html(options);
                        } else {
                            $(targetSelect).html('<option value="">Failed to load users</option>');
                        }
                    },
                    error: function() {
                        $(targetSelect).html('<option value="">Error fetching users</option>');
                    }
                });
            }

            const homeownerLocationSelect = $('select[name="settings[homeowner_clone_location_id]"]');
            const homeownerUserSelect = $('select[name="settings[homeowner_clone_user_id]"]');
            const homeownerLocationId = homeownerLocationSelect.val();
            const homeownerSelectedUserId = "{{ $settings['homeowner_clone_user_id'] ?? '' }}";

            if (homeownerLocationId) {
                loadUsersByLocation(homeownerLocationId, homeownerUserSelect, homeownerSelectedUserId);
            }

            homeownerLocationSelect.on('change', function() {
                loadUsersByLocation($(this).val(), homeownerUserSelect);
            });

            const advisorLocationSelect = $('select[name="settings[advisor_clone_location_id]"]');
            const advisorUserSelect = $('select[name="settings[advisor_clone_user_id]"]');
            const advisorLocationId = advisorLocationSelect.val();
            const advisorSelectedUserId = "{{ $settings['advisor_clone_user_id'] ?? '' }}";

            if (advisorLocationId) {
                loadUsersByLocation(advisorLocationId, advisorUserSelect, advisorSelectedUserId);
            }

            advisorLocationSelect.on('change', function() {
                loadUsersByLocation($(this).val(), advisorUserSelect);
            });

        });
    </script>
@endpush
