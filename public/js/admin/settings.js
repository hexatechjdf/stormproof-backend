$(document).ready(function () {

    /* -------------------------------
     * LOAD USERS BY LOCATION
     * ----------------------------- */
    function loadUsers(locationId, target, selectedId = null) {
        const userSelect = $(`.js-user[data-target="${target}"]`);

        if (!locationId) {
            userSelect.html('<option value="">-- Select CRM User --</option>');
            return;
        }

        userSelect.html('<option>Loading...</option>');

        $.get(`/admin/crm/users/${locationId}`, function (response) {
            let options = '<option value="">-- Select CRM User --</option>';

            if (response.data?.length) {
                response.data.forEach(user => {
                    const selected = selectedId == user.id ? 'selected' : '';
                    options += `<option value="${user.id}" ${selected}>${user.name}</option>`;
                });
            } else {
                options += '<option value="">No users found</option>';
            }

            userSelect.html(options);
        });
    }

    $('.js-location').on('change', function () {
        loadUsers($(this).val(), $(this).data('target'));
    });

    $('.js-location').each(function () {
        const target = $(this).data('target');
        const selectedUser = window.existingUsers[target];
        if (this.value) {
            loadUsers(this.value, target, selectedUser);
        }
    });

    /* -------------------------------
     * PLAN MAPPING
     * ----------------------------- */
    $('.crm-products').each(function () {
        initPlanMapping($(this));
    });

    function initPlanMapping(productSelect) {
        const container = productSelect.closest('.card-body')
            .find('.product-mappings');

        const mappingKey = container.data('mapping-key');
        const existing = window.existingProductPrices[mappingKey] || {};

        productSelect.select2({
            placeholder: 'Select CRM Products',
            width: '100%'
        });

        productSelect.on('change', function () {
            const products = $(this).val() || [];

            container.find('.product-card').each(function () {
                if (!products.includes($(this).data('id'))) {
                    $(this).remove();
                }
            });

            products.forEach(pid => {
                if (!container.find(`[data-id="${pid}"]`).length) {
                    createProductCard(pid);
                }
            });
        });

        var preselectedProducts = Object.keys(existing);

        // 1️⃣ Preselect products FIRST
        if (preselectedProducts.length) {
            productSelect.val(preselectedProducts).trigger('change');
        }

        // 2️⃣ Create cards AFTER products are selected
        preselectedProducts.forEach(function (productId) {
            if (!container.find('[data-id="' + productId + '"]').length) {
                createProductCard(productId);
            }
        });

        function createProductCard(productId) {
            const name = productSelect.find(`option[value="${productId}"]`).text();

            const card = $(`
                <div class="card mb-3 product-card" data-id="${productId}">
                    <div class="card-header d-flex justify-content-between">
                        <strong>${name}</strong>
                        <button type="button" class="btn btn-sm btn-outline-danger">Remove</button>
                    </div>
                    <div class="card-body">
                        <select class="form-select price-select"
                                name="settings[${mappingKey}][${productId}][]"
                                multiple></select>
                    </div>
                </div>
            `);

            container.append(card);

            var priceSelect = card.find('.price-select');

            priceSelect
                .prop('disabled', true)
                .append('<option>Loading prices...</option>')
                .select2({
                    width: '100%',
                    placeholder: 'Loading prices...',
                    templateResult: function (state) {
                        if (!state.id) {
                            return $('<span class="d-flex align-items-center">' +
                                '<span class="spinner-border spinner-border-sm me-2"></span>' +
                                'Loading prices…</span>');
                        }
                        return state.text;
                    }
                });

            fetch(`/admin/crm/products/${productId}/prices`)
                .then(res => res.json())
                .then(res => {
                    res.data?.forEach(price => {
                        priceSelect.empty();

                        res.data.forEach(function (price) {
                            priceSelect.append(
                                new Option(
                                    price.name + ' — ' + price.currency + ' ' + price.amount,
                                    price._id,
                                    false,
                                    false
                                )
                            );
                        });

                        priceSelect.prop('disabled', false).trigger('change');

                        // Preselect saved prices (EDIT MODE)
                        if (existing[productId]) {
                            priceSelect.val(existing[productId]).trigger('change');
                        }
                    });

                    if (existing[productId]) {
                        priceSelect.val(existing[productId]).trigger('change');
                    }
                });

            card.find('button').on('click', function () {
                card.remove();
                productSelect.find(`option[value="${productId}"]`).prop('selected', false);
                productSelect.trigger('change');
            });
        }
    }
});
