<div class="row g-3">
    <div class="col-md-8">
        <label class="form-label fw-semibold">Nombre <span class="text-danger">*</span></label>
        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
               value="{{ old('name', $product->name ?? '') }}" required>
        @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-4">
        <label class="form-label fw-semibold">SKU <span class="text-danger">*</span></label>
        <input type="text" name="sku" class="form-control @error('sku') is-invalid @enderror"
               value="{{ old('sku', $product->sku ?? '') }}" required>
        @error('sku') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-12">
        <label class="form-label fw-semibold">Descripción</label>
        <textarea name="description" class="form-control @error('description') is-invalid @enderror" rows="3">{{ old('description', $product->description ?? '') }}</textarea>
        @error('description') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-4">
        <label class="form-label fw-semibold">Categoría <span class="text-danger">*</span></label>
        <select name="category_id" class="form-select @error('category_id') is-invalid @enderror" required>
            <option value="">Seleccionar...</option>
            @foreach($categories as $cat)
                <option value="{{ $cat->id }}" {{ old('category_id', $product->category_id ?? '') == $cat->id ? 'selected' : '' }}>
                    {{ $cat->name }}
                </option>
                @foreach($cat->children as $child)
                    <option value="{{ $child->id }}" {{ old('category_id', $product->category_id ?? '') == $child->id ? 'selected' : '' }}>
                        &nbsp;&nbsp;&nbsp;↳ {{ $child->name }}
                    </option>
                @endforeach
            @endforeach
        </select>
        @error('category_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-4">
        <label class="form-label fw-semibold">Precio (S/) <span class="text-danger">*</span></label>
        <input type="number" step="0.01" name="price" class="form-control @error('price') is-invalid @enderror"
               value="{{ old('price', $product->price ?? '') }}" required>
        @error('price') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-4">
        <label class="form-label fw-semibold">Costo (S/)</label>
        <input type="number" step="0.01" name="cost" class="form-control @error('cost') is-invalid @enderror"
               value="{{ old('cost', $product->cost ?? '') }}">
        @error('cost') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-3">
        <label class="form-label fw-semibold">Unidad <span class="text-danger">*</span></label>
        <select name="unit" class="form-select @error('unit') is-invalid @enderror">
            @foreach(['unit' => 'Unidad', 'kg' => 'Kilogramo', 'g' => 'Gramo', 'lt' => 'Litro', 'ml' => 'Mililitro', 'portion' => 'Porción'] as $val => $label)
                <option value="{{ $val }}" {{ old('unit', $product->unit ?? 'unit') == $val ? 'selected' : '' }}>{{ $label }}</option>
            @endforeach
        </select>
        @error('unit') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-3">
        <label class="form-label fw-semibold">Stock mínimo</label>
        <input type="number" name="minimum_stock" class="form-control @error('minimum_stock') is-invalid @enderror"
               value="{{ old('minimum_stock', $product->minimum_stock ?? 5) }}" min="0">
        @error('minimum_stock') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-3">
        <label class="form-label fw-semibold">Imagen</label>
        <input type="file" name="image" class="form-control @error('image') is-invalid @enderror" accept="image/*">
        @error('image') <div class="invalid-feedback">{{ $message }}</div> @enderror
        @if(isset($product) && $product->image)
            <div class="mt-2">
                <img src="{{ $product->image_url }}" alt="Imagen actual" style="height:60px;border-radius:6px;">
            </div>
        @endif
    </div>

    <div class="col-md-3 d-flex align-items-end">
        <div class="form-check form-switch">
            <input class="form-check-input" type="checkbox" name="is_active" value="1"
                   {{ old('is_active', $product->is_active ?? true) ? 'checked' : '' }}>
            <label class="form-check-label fw-semibold">Activo</label>
        </div>
    </div>
</div>

<!-- expiry Fields -->
<div class="row g-3 mt-2">
    <div class="col-12">
        <h6 class="text-muted border-bottom pb-2">
            <i class="bi bi-calendar-check me-1"></i>Información de Vencimiento
        </h6>
    </div>

    <div class="col-md-3">
        <div class="form-check form-switch">
            <input class="form-check-input" type="checkbox" name="is_perishable" value="1"
                   {{ old('is_perishable', $product->is_perishable ?? false) ? 'checked' : '' }}
                   id="is_perishable" onchange="toggleExpiryFields()">
            <label class="form-check-label fw-semibold" for="is_perishable">Producto Perecible</label>
        </div>
    </div>

    <div class="col-md-3 expiry-field" style="display: {{ old('is_perishable', $product->is_perishable ?? false) ? 'block' : 'none' }}">
        <label class="form-label fw-semibold">Fecha de Vencimiento</label>
        <input type="date" name="expiry_date" class="form-control @error('expiry_date') is-invalid @enderror"
               value="{{ old('expiry_date', $product->expiry_date ?? '') }}">
        @error('expiry_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-3 expiry-field" style="display: {{ old('is_perishable', $product->is_perishable ?? false) ? 'block' : 'none' }}">
        <label class="form-label fw-semibold">Días en Estante</label>
        <input type="number" name="shelf_days" class="form-control @error('shelf_days') is-invalid @enderror"
               value="{{ old('shelf_days', $product->shelf_days ?? '') }}" min="1" placeholder="Días antes de vencer">
        @error('shelf_days') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>
</div>

@push('scripts')
<script>
function toggleExpiryFields() {
    const isPerishable = document.getElementById('is_perishable').checked;
    const expiryFields = document.querySelectorAll('.expiry-field');
    expiryFields.forEach(field => {
        field.style.display = isPerishable ? 'block' : 'none';
        if (!isPerishable) {
            const input = field.querySelector('input');
            if (input) input.value = '';
        }
    });
}
</script>
@endpush
