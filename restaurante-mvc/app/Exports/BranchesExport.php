<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class BranchesExport implements FromCollection, WithHeadings
{
    protected $branches;

    public function __construct($branches)
    {
        $this->branches = $branches;
    }

    public function collection()
    {
        return $this->branches->map(function ($branch) {
            return [
                'Código' => $branch->code,
                'Nombre' => $branch->name,
                'Dirección' => $branch->address,
                'Ciudad' => $branch->city,
                'Teléfono' => $branch->phone,
                'Email' => $branch->email,
                'Gerente' => $branch->manager_name,
                'Latitud' => $branch->latitude,
                'Longitud' => $branch->longitude,
                'Productos' => $branch->products_count,
                'Estado' => $branch->is_active ? 'Activo' : 'Inactivo',
            ];
        });
    }

    public function headings(): array
    {
        return ['Código', 'Nombre', 'Dirección', 'Ciudad', 'Teléfono', 'Email', 'Gerente', 'Latitud', 'Longitud', 'Productos', 'Estado'];
    }
}
