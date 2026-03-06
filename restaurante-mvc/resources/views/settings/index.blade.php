@extends('layouts.app')

@section('title', 'Configuración')

@section('module', 'Configuración')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="fw-bold mb-0">
        <i class="bi bi-gear me-2"></i>Configuración del Sistema
    </h4>
</div>

<div class="row">
    <div class="col-md-12">
        <form method="POST" action="{{ route('settings.update') }}">
            @csrf
            @method('PUT')

            <!-- Configuración de Impuestos -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-percent me-2"></i>Configuración de Impuestos</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Nombre del Impuesto</label>
                            <input type="text" name="tax_igv_name" class="form-control"
                                   value="{{ $settings['tax_igv_name']->value ?? 'IGV' }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Tasa (%)</label>
                            <input type="number" name="tax_igv_rate" class="form-control" step="0.01"
                                   value="{{ $settings['tax_igv_rate']->value ?? 18 }}">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Configuración de Empresa -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-building me-2"></i>Información de la Empresa</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Nombre</label>
                            <input type="text" name="company_name" class="form-control"
                                   value="{{ $settings['company_name']->value ?? '' }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">RUC</label>
                            <input type="text" name="company_ruc" class="form-control"
                                   value="{{ $settings['company_ruc']->value ?? '' }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Teléfono</label>
                            <input type="text" name="company_phone" class="form-control"
                                   value="{{ $settings['company_phone']->value ?? '' }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Email</label>
                            <input type="email" name="company_email" class="form-control"
                                   value="{{ $settings['company_email']->value ?? '' }}">
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">Dirección</label>
                            <textarea name="company_address" class="form-control" rows="2">{{ $settings['company_address']->value ?? '' }}</textarea>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Configuración de Moneda -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-currency-exchange me-2"></i>Configuración de Moneda</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Símbolo de Moneda</label>
                            <input type="text" name="currency_symbol" class="form-control"
                                   value="{{ $settings['currency_symbol']->value ?? 'S/' }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Código de Moneda</label>
                            <input type="text" name="currency_code" class="form-control"
                                   value="{{ $settings['currency_code']->value ?? 'PEN' }}">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Configuración de Inventario -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-box-seam me-2"></i>Configuración de Inventario</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Stock Mínimo por Defecto</label>
                            <input type="number" name="low_stock_threshold" class="form-control"
                                   value="{{ $settings['low_stock_threshold']->value ?? 5 }}">
                            <small class="text-muted">Cantidad mínima de stock para alertas</small>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Días de Advertencia de Vencimiento</label>
                            <input type="number" name="expiry_warning_days" class="form-control"
                                   value="{{ $settings['expiry_warning_days']->value ?? 7 }}">
                            <small class="text-muted">Días antes del vencimiento para mostrar advertencia</small>
                        </div>
                    </div>
                </div>
            </div>

            <div class="text-end">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-save me-1"></i>Guardar Configuración
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
