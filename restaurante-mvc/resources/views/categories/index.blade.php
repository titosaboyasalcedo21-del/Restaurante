@extends('layouts.app')

@section('title', 'Categorías')

@section('module', 'Categorías')

@section('content')
<!-- Page Header -->
<div class="page-header mb-4">
    <h3 class="page-title">
        <i class="bi bi-tags"></i>
        Categorías
    </h3>
    @can('create', App\Models\Category::class)
    <a href="{{ route('categories.create') }}" class="btn-primary-custom">
        <i class="bi bi-plus-lg"></i>
        Nueva Categoría
    </a>
    @endcan
</div>

<!-- Categories List -->
@forelse($categories as $category)
<div class="category-card">
    <div class="category-header" onclick="toggleCategory('category-{{ $category->id }}')">
        <div class="category-info">
            <div class="category-icon">
                <i class="bi bi-collection"></i>
            </div>
            <div>
                <div class="category-name">{{ $category->name }}</div>
                <div class="text-muted" style="font-size: 0.75rem;">{{ $category->description ?? 'Sin descripción' }}</div>
            </div>
        </div>
        <div class="d-flex align-items-center gap-3">
            <span class="category-count">{{ $category->products->count() }} productos</span>
            <i class="bi bi-chevron-down" id="icon-category-{{ $category->id }}"></i>
        </div>
    </div>

    <div class="category-body" id="category-{{ $category->id }}" style="display: none;">
        @if($category->products->isNotEmpty())
        <div class="subcategory-pills">
            @foreach($category->products as $product)
            <span class="subcategory-pill">{{ $product->name }}</span>
            @endforeach
        </div>
        @else
        <p class="text-muted" style="font-size: 0.85rem;">No hay productos en esta categoría</p>
        @endif

        <div class="d-flex gap-2 mt-3">
            <a href="{{ route('categories.show', $category) }}" class="btn-dark-sm">
                <i class="bi bi-eye"></i> Ver
            </a>
            @can('update', $category)
            <a href="{{ route('categories.edit', $category) }}" class="btn-red-sm">
                <i class="bi bi-pencil"></i> Editar
            </a>
            @endcan
            @can('delete', $category)
            <form method="POST" action="{{ route('categories.destroy', $category) }}" class="d-inline" onsubmit="return confirm('¿Eliminar esta categoría?')">
                @csrf @method('DELETE')
                <button type="submit" class="btn-danger-sm">
                    <i class="bi bi-trash"></i> Eliminar
                </button>
            </form>
            @endcan
        </div>
    </div>
</div>
@empty
<div class="dark-card">
    <div class="empty-state">
        <i class="bi bi-tags"></i>
        <p>No hay categorías registradas</p>
        @can('create', App\Models\Category::class)
        <a href="{{ route('categories.create') }}" class="btn-primary-custom mt-3">
            <i class="bi bi-plus-lg"></i>
            Crear Primera Categoría
        </a>
        @endcan
    </div>
</div>
@endforelse

<script>
function toggleCategory(id) {
    const body = document.getElementById(id);
    const icon = document.getElementById('icon-' + id);
    if (body.style.display === 'none') {
        body.style.display = 'block';
        icon.style.transform = 'rotate(180deg)';
    } else {
        body.style.display = 'none';
        icon.style.transform = 'rotate(0deg)';
    }
}
</script>
@endsection
