@extends('layouts.admin')

@section('title', 'Settings')

@section('content')
    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="row g-4">

        {{-- HOMEOWNER CREATION MAPPING --}}
        <div class="col-lg-6">
            <form method="POST" action="{{ route('admin.settings.update', Auth::user()->agency_id) }}">
                @csrf
                @method('PUT')

                <div class="card h-100">
                    <div class="card-header fw-semibold">
                        Homeowner Creation Mapping
                    </div>

                    <div class="card-body">
                        <p class="text-muted">
                            Clone a <strong>Homeowner</strong> when a CRM user is created in the selected location.
                        </p>

                        <div class="mb-3">
                            <label class="form-label">CRM Location</label>
                            <select name="settings[homeowner_clone_location_id]" class="form-select js-location"
                                data-target="homeowner">
                                <option value="">-- Select CRM Location --</option>
                                @foreach ($crmLocations as $location)
                                    <option value="{{ $location->id }}" @selected(($settings['homeowner_clone_location_id'] ?? '') == $location->id)>
                                        {{ $location->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Clone from CRM User</label>
                            <select name="settings[homeowner_clone_user_id]" class="form-select js-user"
                                data-target="homeowner">
                                <option value="">-- Select CRM User --</option>
                            </select>
                        </div>
                    </div>

                    <div class="card-footer text-end">
                        <button class="btn btn-primary">
                            Save Homeowner Mapping
                        </button>
                    </div>
                </div>
            </form>
        </div>

        {{-- ADVISOR CREATION MAPPING --}}
        <div class="col-lg-6">
            <form method="POST" action="{{ route('admin.settings.update', Auth::user()->agency_id) }}">
                @csrf
                @method('PUT')

                <div class="card h-100">
                    <div class="card-header fw-semibold">
                        Advisor Creation Mapping
                    </div>

                    <div class="card-body">
                        <p class="text-muted">
                            Clone an <strong>Advisor</strong> when a CRM user is created in the selected location.
                        </p>

                        <div class="mb-3">
                            <label class="form-label">CRM Location</label>
                            <select name="settings[advisor_clone_location_id]" class="form-select js-location"
                                data-target="advisor">
                                <option value="">-- Select CRM Location --</option>
                                @foreach ($crmLocations as $location)
                                    <option value="{{ $location->id }}" @selected(($settings['advisor_clone_location_id'] ?? '') == $location->id)>
                                        {{ $location->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Clone from CRM User</label>
                            <select name="settings[advisor_clone_user_id]" class="form-select js-user"
                                data-target="advisor">
                                <option value="">-- Select CRM User --</option>
                            </select>
                        </div>
                    </div>

                    <div class="card-footer text-end">
                        <button class="btn btn-primary">
                            Save Advisor Mapping
                        </button>
                    </div>
                </div>
            </form>
        </div>

    </div>

    {{-- PLAN MAPPING --}}
    <div class="row g-4 mt-5">

        {{-- HOMEOWNER PLAN --}}
        <div class="col-lg-6">
            <form method="POST" action="{{ route('admin.settings.update', Auth::user()->agency_id) }}">
                @csrf
                @method('PUT')
                <input type="hidden" name="plansMappingForm" value="homeowner">

                <div class="card">
                    <div class="card-header fw-semibold">Homeowner Plan Mapping</div>
                    <div class="card-body">
                        <p class="text-muted">
                            Plans used during <strong>Homeowner</strong> account creation.
                        </p>

                        @include('admin.settings.partials.plan-mapping', [
                            'selectId' => 'homeownerProducts',
                            'mappingKey' => 'homeowner_product_prices',
                        ])
                    </div>
                    <div class="card-footer text-end">
                        <button class="btn btn-primary">Save Homeowner Plans</button>
                    </div>
                </div>
            </form>
        </div>

        {{-- ADVISOR PLAN --}}
        <div class="col-lg-6">
            <form method="POST" action="{{ route('admin.settings.update', Auth::user()->agency_id) }}">
                @csrf
                @method('PUT')
                <input type="hidden" name="plansMappingForm" value="advisor">

                <div class="card">
                    <div class="card-header fw-semibold">Advisor Plan Mapping</div>
                    <div class="card-body">
                        <p class="text-muted">
                            Plans used during <strong>Advisor</strong> account creation.
                        </p>

                        @include('admin.settings.partials.plan-mapping', [
                            'selectId' => 'advisorProducts',
                            'mappingKey' => 'advisor_product_prices',
                        ])
                    </div>
                    <div class="card-footer text-end">
                        <button class="btn btn-primary">Save Advisor Plans</button>
                    </div>
                </div>
            </form>
        </div>

    </div>
@endsection

@push('scripts')
    <script>
        window.existingProductPrices = @json(isset($selectedMappings) ? $selectedMappings : []);
        window.existingUsers = {
            homeowner: "{{ isset($settings['homeowner_clone_user_id']) ? $settings['homeowner_clone_user_id'] : '' }}",
            advisor: "{{ isset($settings['advisor_clone_user_id']) ? $settings['advisor_clone_user_id'] : '' }}"
        };
    </script>

    <script src="{{ asset('js/admin/settings.js') }}"></script>
@endpush
