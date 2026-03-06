<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class CategoriesExport implements FromCollection, WithHeadings
{
    protected $categories;

    public function __construct($categories)
    {
        $this->categories = $categories;
    }

    public function collection()
    {
        return $this->categories->map(function ($category) {
            return [
                'Nombre' => $category->name,
                'Descripción' => $category->description,
                'Categoría Padre' => $category->parent?->name,
                'Orden' => $category->order,
                'Estado' => $category->is_active ? 'Activo' : 'Inactivo',
            ];
        });
    }

    public function headings(): array
    {
        return ['Nombre', 'Descripción', 'Categoría Padre', 'Orden', 'Estado'];
    }
}
