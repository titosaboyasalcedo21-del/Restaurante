<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class InventoryExport implements FromCollection, WithHeadings
{
    protected $branches;

    public function __construct($branches)
    {
        $this->branches = $branches;
    }

    public function collection()
    {
        $data = [];

        foreach ($this->branches as $branch) {
            foreach ($branch->products as $product) {
                $data[] = [
                    'Sucursal' => $branch->name,
                    'Código Producto' => $product->sku,
                    'Producto' => $product->name,
                    'Categoría' => $product->category?->name,
                    'Stock' => $product->pivot->stock,
                    'Stock Mínimo' => $product->minimum_stock,
                    'Disponibilidad' => $product->pivot->is_available ? 'Disponible' : 'No disponible',
                ];
            }
        }

        return collect($data);
    }

    public function headings(): array
    {
        return ['Sucursal', 'Código Producto', 'Producto', 'Categoría', 'Stock', 'Stock Mínimo', 'Disponibilidad'];
    }
}
