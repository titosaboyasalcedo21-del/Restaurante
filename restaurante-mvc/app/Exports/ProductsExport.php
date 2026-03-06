<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class ProductsExport implements FromCollection, WithHeadings
{
    protected $products;

    public function __construct($products)
    {
        $this->products = $products;
    }

    public function collection()
    {
        return $this->products->map(function ($product) {
            return [
                'SKU' => $product->sku,
                'Nombre' => $product->name,
                'Descripción' => $product->description,
                'Categoría' => $product->category?->name,
                'Proveedor' => $product->supplier?->name,
                'Precio' => $product->price,
                'Costo' => $product->cost,
                'Stock Mínimo' => $product->minimum_stock,
                'Unidad' => $product->unit,
                'Estado' => $product->is_active ? 'Activo' : 'Inactivo',
            ];
        });
    }

    public function headings(): array
    {
        return ['SKU', 'Nombre', 'Descripción', 'Categoría', 'Proveedor', 'Precio', 'Costo', 'Stock Mínimo', 'Unidad', 'Estado'];
    }
}
