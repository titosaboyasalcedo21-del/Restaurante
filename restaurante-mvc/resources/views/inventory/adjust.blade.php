@extends('layouts.app')

@section('title', 'Ajustar Stock')

@section('module', 'Inventario')

@section('content')
<!-- Page Header -->
<div class="page-header mb-4">
    <h3 class="page-title">
        <i class="bi bi-sliders"></i>
        Ajustar Stock
    </h3>
    <a href="{{ route('inventory.movements') }}" class="btn-outline-custom">
        <i class="bi bi-arrow-left"></i> Volver
    </a>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const typeSelect = document.querySelector('select[name="type"]');
    const destField = document.getElementById('destination-branch-field');

    if (typeSelect) {
        typeSelect.addEventListener('change', function() {
            if (this.value === 'transfer') {
                destField.style.display = 'block';
            } else {
                destField.style.display = 'none';
            }
        });
    }

    // Set initial state based on pre-selected type
    if (typeSelect && typeSelect.value === 'transfer') {
        destField.style.display = 'block';
    }
});
</script>

<div class="row justify-content-center">
    <div class="col-md-7">
        <div class="dark-card">
            <div class="dark-card-body">
                <form method="POST" action="{{ route('inventory.adjust') }}">
                    @csrf
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label" style="color: var(--text-secondary); font-weight: 500;">Sucursal <span style="color: var(--accent-red);">*</span></label>
                            <select name="branch_id" class="form-select-dark w-100 @error('branch_id') is-invalid @enderror" required>
                                <option value="">Seleccionar sucursal...</option>
                                @foreach($branches as $branch)
                                    <option value="{{ $branch->id }}" {{ old('branch_id') == $branch->id ? 'selected' : '' }}>
                                        {{ $branch->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('branch_id') <div class="text-danger" style="font-size: 0.8rem;">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-12">
                            <label class="form-label" style="color: var(--text-secondary); font-weight: 500;">Producto <span style="color: var(--accent-red);">*</span></label>
                            <select name="product_id" class="form-select-dark w-100 @error('product_id') is-invalid @enderror" required>
                                <option value="">Seleccionar producto...</option>
                                @foreach($products as $product)
                                    <option value="{{ $product->id }}" {{ old('product_id') == $product->id ? 'selected' : '' }}>
                                        {{ $product->name }} ({{ $product->sku }})
                                    </option>
                                @endforeach
                            </select>
                            @error('product_id') <div class="text-danger" style="font-size: 0.8rem;">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label" style="color: var(--text-secondary); font-weight: 500;">Tipo <span style="color: var(--accent-red);">*</span></label>
                            <select name="type" class="form-select-dark w-100 @error('type') is-invalid @enderror" required>
                                @if(auth()->user()->isManager() || auth()->user()->isAdmin())
                                <option value="in" {{ old('type') == 'in' ? 'selected' : '' }}>📦 Entrada</option>
                                @endif
                                <option value="out" {{ old('type') == 'out' ? 'selected' : '' }}>🔻 Salida</option>
                                @if(auth()->user()->isManager() || auth()->user()->isAdmin())
                                <option value="adjust" {{ old('type') == 'adjust' ? 'selected' : '' }}>⚖️ Ajuste</option>
                                <option value="transfer" {{ old('type') == 'transfer' ? 'selected' : '' }}>🔄 Transferencia</option>
                                @endif
                            </select>
                            @error('type') <div class="text-danger" style="font-size: 0.8rem;">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label" style="color: var(--text-secondary); font-weight: 500;">Cantidad <span style="color: var(--accent-red);">*</span></label>
                            <input type="number" name="quantity" class="form-control-dark w-100 @error('quantity') is-invalid @enderror"
                                   value="{{ old('quantity', 1) }}" min="1" required>
                            @error('quantity') <div class="text-danger" style="font-size: 0.8rem;">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label" style="color: var(--text-secondary); font-weight: 500;">Razón</label>
                            <input type="text" name="reason" class="form-control-dark w-100 @error('reason') is-invalid @enderror"
                                   value="{{ old('reason') }}" placeholder="Ej: Compra a proveedor, merma...">
                            @error('reason') <div class="text-danger" style="font-size: 0.8rem;">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label" style="color: var(--text-secondary); font-weight: 500;">Referencia</label>
                            <input type="text" name="reference" class="form-control-dark w-100 @error('reference') is-invalid @enderror"
                                   value="{{ old('reference') }}" placeholder="Ej: Factura #001">
                            @error('reference') <div class="text-danger" style="font-size: 0.8rem;">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-12" id="destination-branch-field" style="display: none;">
                            <label class="form-label" style="color: var(--text-secondary); font-weight: 500;">Sucursal de Destino <span style="color: var(--accent-red);">*</span></label>
                            <select name="destination_branch_id" class="form-select-dark w-100 @error('destination_branch_id') is-invalid @enderror">
                                <option value="">Seleccionar sucursal destino...</option>
                                @foreach($branches as $branch)
                                    <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                                @endforeach
                            </select>
                            @error('destination_branch_id') <div class="text-danger" style="font-size: 0.8rem;">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-12 mt-4">
                            <button type="submit" class="btn-primary-custom">
                                <i class="bi bi-check-lg"></i>Registrar Movimiento
                            </button>
                            <a href="{{ route('inventory.movements') }}" class="btn-outline-custom ms-2">Cancelar</a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
