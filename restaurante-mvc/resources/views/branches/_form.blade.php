<div class="row g-3">
    <div class="col-md-8">
        <label class="form-label fw-semibold">Nombre <span class="text-danger">*</span></label>
        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
               value="{{ old('name', $branch->name ?? '') }}" required>
        @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>
    <div class="col-md-4">
        <label class="form-label fw-semibold">Código <span class="text-danger">*</span></label>
        <input type="text" name="code" class="form-control font-monospace @error('code') is-invalid @enderror"
               value="{{ old('code', $branch->code ?? '') }}" required>
        @error('code') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>
    <div class="col-md-8">
        <label class="form-label fw-semibold">Dirección</label>
        <input type="text" name="address" class="form-control @error('address') is-invalid @enderror"
               value="{{ old('address', $branch->address ?? '') }}">
        @error('address') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>
    <div class="col-md-4">
        <label class="form-label fw-semibold">Ciudad</label>
        <input type="text" name="city" class="form-control @error('city') is-invalid @enderror"
               value="{{ old('city', $branch->city ?? '') }}">
        @error('city') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>
    <div class="col-md-4">
        <label class="form-label fw-semibold">Teléfono</label>
        <input type="text" name="phone" class="form-control @error('phone') is-invalid @enderror"
               value="{{ old('phone', $branch->phone ?? '') }}">
        @error('phone') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>
    <div class="col-md-4">
        <label class="form-label fw-semibold">Email</label>
        <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
               value="{{ old('email', $branch->email ?? '') }}">
        @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>
    <div class="col-md-4">
        <label class="form-label fw-semibold">Gerente</label>
        <input type="text" name="manager_name" class="form-control @error('manager_name') is-invalid @enderror"
               value="{{ old('manager_name', $branch->manager_name ?? '') }}">
        @error('manager_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>
    <div class="col-md-3">
        <label class="form-label fw-semibold">Latitud</label>
        <input type="number" step="0.00000001" name="latitude" class="form-control @error('latitude') is-invalid @enderror"
               value="{{ old('latitude', $branch->latitude ?? '') }}">
        @error('latitude') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>
    <div class="col-md-3">
        <label class="form-label fw-semibold">Longitud</label>
        <input type="number" step="0.00000001" name="longitude" class="form-control @error('longitude') is-invalid @enderror"
               value="{{ old('longitude', $branch->longitude ?? '') }}">
        @error('longitude') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>
    <div class="col-md-3 d-flex align-items-end">
        <div class="form-check form-switch">
            <input class="form-check-input" type="checkbox" name="is_active" value="1"
                   {{ old('is_active', $branch->is_active ?? true) ? 'checked' : '' }}>
            <label class="form-check-label fw-semibold">Activa</label>
        </div>
    </div>
</div>
