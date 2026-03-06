@extends('layouts.app')
@section('title', 'Reporte de Inventario')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('inventory.index') }}">Inventario</a></li>
    <li class="breadcrumb-item active">Reporte</li>
@endsection
@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="fw-bold mb-0"><i class="bi bi-file-earmark-bar-graph me-2 text-primary"></i>Reporte de Movimientos</h4>
</div>

<div class="card mb-4">
    <div class="card-body">
        <form method="GET" class="row g-2">
            <div class="col-md-3">
                <label class="form-label small">Desde</label>
                <input type="date" name="date_from" class="form-control" value="{{ $dateFrom }}">
            </div>
            <div class="col-md-3">
                <label class="form-label small">Hasta</label>
                <input type="date" name="date_to" class="form-control" value="{{ $dateTo }}">
            </div>
            <div class="col-md-3">
                <label class="form-label small">Sucursal</label>
                <select name="branch_id" class="form-select">
                    <option value="">Todas</option>
                    @foreach($branches as $b)
                        <option value="{{ $b->id }}" {{ request('branch_id') == $b->id ? 'selected' : '' }}>{{ $b->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label small">Tipo</label>
                <select name="type" class="form-select">
                    <option value="">Todos</option>
                    <option value="in">Entrada</option>
                    <option value="out">Salida</option>
                    <option value="adjust">Ajuste</option>
                    <option value="transfer">Transferencia</option>
                </select>
            </div>
            <div class="col-12">
                <button type="submit" class="btn btn-primary"><i class="bi bi-search me-1"></i>Generar Reporte</button>
            </div>
        </form>
    </div>
</div>

<!-- Summary -->
<div class="row g-3 mb-4">
    <div class="col-sm-6 col-md-3">
        <div class="card text-center border-0 bg-success bg-opacity-10">
            <div class="card-body">
                <div class="fs-4 fw-bold text-success">{{ $summary['in'] ?? 0 }}</div>
                <div class="text-muted small">Entradas</div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-md-3">
        <div class="card text-center border-0 bg-danger bg-opacity-10">
            <div class="card-body">
                <div class="fs-4 fw-bold text-danger">{{ $summary['out'] ?? 0 }}</div>
                <div class="text-muted small">Salidas</div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-md-3">
        <div class="card text-center border-0 bg-warning bg-opacity-10">
            <div class="card-body">
                <div class="fs-4 fw-bold text-warning">{{ $summary['adjust'] ?? 0 }}</div>
                <div class="text-muted small">Ajustes</div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-md-3">
        <div class="card text-center border-0 bg-info bg-opacity-10">
            <div class="card-body">
                <div class="fs-4 fw-bold text-info">{{ $movements->count() }}</div>
                <div class="text-muted small">Total Movimientos</div>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-body p-0">
        <table class="table table-sm table-hover mb-0">
            <thead class="table-light">
                <tr><th>Fecha</th><th>Producto</th><th>Sucursal</th><th>Tipo</th><th>Cant.</th><th>Stock Prev.</th><th>Stock Nuevo</th><th>Razón</th></tr>
            </thead>
            <tbody>
                @forelse($movements as $m)
                <tr>
                    <td><small>{{ $m->created_at->format('d/m/Y H:i') }}</small></td>
                    <td>{{ $m->product->name ?? '—' }}</td>
                    <td>{{ $m->branch->name ?? '—' }}</td>
                    <td>
                        @php $colors = ['in' => 'badge-in', 'out' => 'badge-out', 'adjust' => 'badge-adjust', 'transfer' => 'badge-transfer']; @endphp
                        <span class="badge {{ $colors[$m->type] ?? 'bg-secondary' }}">{{ $m->type_label }}</span>
                    </td>
                    <td>{{ $m->quantity }}</td>
                    <td class="text-muted">{{ $m->previous_stock }}</td>
                    <td class="fw-semibold">{{ $m->new_stock }}</td>
                    <td><small class="text-muted">{{ $m->reason ?? '—' }}</small></td>
                </tr>
                @empty
                <tr><td colspan="8" class="text-center py-4 text-muted">Sin movimientos en el período seleccionado.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
