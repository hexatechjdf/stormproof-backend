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
    </form>

    <form method="POST" action="{{ route('admin.settings.update', ['agency' => Auth::user()->agency_id]) }}">
        @csrf
        <input type="hidden" name="plansMappingForm" value="true" />
        <div class="col-lg-8">
            <div class="card mt-3">
                <div class="card-header fw-bold">
                    Plans Mapping
                </div>

                <div class="card-body">
                    <p class="text-muted mb-4">
                        Select CRM products and configure multiple prices for each product.
                    </p>

                    <!-- Products -->
                    <div class="mb-4">
                        <label class="form-label fw-semibold">
                            CRM Products
                        </label>

                        <select id="crmProductsSelect" class="form-select" multiple>
                            @foreach ($crmProducts as $product)
                                <option value="{{ $product->_id }}">
                                    {{ $product->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Product → Prices Mapping -->
                    <div id="productPriceMappings"></div>
                </div>
            </div>
        </div>
        <div class="mt-3">
            <button type="submit" class="btn btn-primary">Save</button>
        </div>
    </form>

    </div>
@endsection
@push('scripts')
    <script>
        window.existingProductPrices = @json($selectedMappings);
    </script>
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
    <script>
        document.addEventListener('DOMContentLoaded', function() {

            const productSelect = $('#crmProductsSelect');
            const mappingsContainer = $('#productPriceMappings');
            const existingMappings = window.existingProductPrices || {};
            // Init Select2 for products
            productSelect.select2({
                placeholder: 'Select CRM Products',
                width: '100%'
            });
            const preselectedProducts = Object.keys(existingMappings);
            // Preselect products (EDIT MODE)
            if (preselectedProducts.length) {
                preselectedProducts.forEach(pid => createProductCard(pid));
                productSelect.val(preselectedProducts).trigger('change');
            }

            productSelect.on('change', function() {
                const selectedProductIds = $(this).val() || [];
                // Remove unselected cards
                $('.product-mapping').each(function() {
                    const productId = $(this).data('product-id');
                    if (!selectedProductIds.includes(productId)) {
                        $(this).remove();
                    }
                });

                // Add new cards
                selectedProductIds.forEach(productId => {
                    if (!$(`[data-product-id="${productId}"]`).length) {
                        createProductCard(productId);
                    }
                });
            });

            function createProductCard(productId) {
                const productName = productSelect.find(`option[value="${productId}"]`).text();

                const card = $(`
            <div class="card mb-3 product-mapping" data-product-id="${productId}">
                <div class="card-header d-flex justify-content-between">
                    <strong>${productName}</strong>
                    <button type="button" class="btn btn-sm btn-outline-danger remove-product">
                        Remove
                    </button>
                </div>
                <div class="card-body">
                    <label class="form-label fw-semibold">Prices</label>
                    <select
                        name="settings[product_prices][${productId}][]"
                        class="form-select price-select"
                        multiple
                    ></select>
                </div>
            </div>
        `);

                mappingsContainer.append(card);

                const priceSelect = card.find('.price-select');

                priceSelect.select2({
                    placeholder: 'Select prices',
                    width: '100%'
                });

                fetchPrices(productId, priceSelect);

                card.find('.remove-product').on('click', function() {
                    card.remove();
                    productSelect.find(`option[value="${productId}"]`).prop('selected', false);
                    productSelect.trigger('change');
                });
            }

            function fetchPrices(productId, priceSelect) {
                priceSelect.empty().append('<option disabled>Loading...</option>');

                fetch(`/admin/crm/products/${productId}/prices`)
                    .then(res => res.json())
                    .then(response => {
                        priceSelect.empty();

                        if (!response.data || !response.data.length) {
                            priceSelect.append('<option disabled>No prices found</option>');
                            return;
                        }

                        response.data.forEach(price => {
                            const label =
                                `${price.name} — ${price.currency} ${price.amount} / ${price.recurring?.interval}`;

                            priceSelect.append(
                                new Option(label, price._id, false, false)
                            );
                        });

                        // Preselect prices (EDIT MODE)
                        if (existingMappings[productId]) {
                            priceSelect.val(existingMappings[productId]).trigger('change');
                        }
                    })
                    .catch(() => {
                        priceSelect.empty().append('<option disabled>Error loading prices</option>');
                    });
            }
        });
    </script>
@endpush
