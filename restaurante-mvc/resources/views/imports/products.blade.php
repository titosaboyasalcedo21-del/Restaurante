@extends('layouts.app')

@section('title', 'Importar Productos')

@section('module', 'Importar Productos')

@section('content')
<div class="page-header mb-4">
    <h3 class="page-title"><i class="bi bi-upload"></i> Importar Productos desde Excel</h3>
    <a href="{{ route('products.index') }}" class="btn btn-secondary">Volver</a>
</div>

<div class="row">
    <div class="col-md-6">
        <div class="card mb-4">
            <div class="card-header">Subir Archivo</div>
            <div class="card-body">
                <form method="POST" action="{{ route('import.products.process') }}" enctype="multipart/form-data">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">Archivo Excel (.xlsx, .xls, .csv)</label>
                        <input type="file" name="file" class="form-control" accept=".xlsx,.xls,.csv" required>
                    </div>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-upload"></i> Importar
                    </button>
                </form>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">Formato Requerido</div>
            <div class="card-body">
                <p class="text-muted">El archivo debe contener las siguientes columnas:</p>
                <table class="table table-sm">
                    <thead>
                        <tr><th>Columna</th><th>Requerido</th></tr>
                    </thead>
                    <tbody>
                        <tr><td>nombre</td><td><span class="badge bg-danger">Sí</span></td></tr>
                        <tr><td>sku</td><td><span class="badge bg-secondary">No</span></td></tr>
                        <tr><td>descripcion</td><td><span class="badge bg-secondary">No</span></td></tr>
                        <tr><td>categoria</td><td><span class="badge bg-secondary">No</span></td></tr>
                        <tr><td>proveedor</td><td><span class="badge bg-secondary">No</span></td></tr>
                        <tr><td>precio</td><td><span class="badge bg-secondary">No</span></td></tr>
                        <tr><td>costo</td><td><span class="badge bg-secondary">No</span></td></tr>
                        <tr><td>stock_minimo</td><td><span class="badge bg-secondary">No</span></td></tr>
                        <tr><td>unidad</td><td><span class="badge bg-secondary">No</span></td></tr>
                    </tbody>
                </table>
                <div class="mt-3">
                    <a href="{{ route('export.products') }}" class="btn btn-outline-primary btn-sm">
                        <i class="bi bi-download"></i> Descargar plantilla actual
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
