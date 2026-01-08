<div class="mb-3">
    <label class="form-label fw-semibold">CRM Products</label>

    <select id="{{ $selectId }}" class="form-select crm-products" multiple>
        @foreach ($crmProducts as $product)
            <option value="{{ $product->_id }}">
                {{ $product->name }}
            </option>
        @endforeach
    </select>
</div>

<div class="product-mappings" data-mapping-key="{{ $mappingKey }}">
</div>
