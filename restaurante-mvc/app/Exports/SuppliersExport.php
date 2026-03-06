<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class SuppliersExport implements FromCollection, WithHeadings
{
    protected $suppliers;

    public function __construct($suppliers)
    {
        $this->suppliers = $suppliers;
    }

    public function collection()
    {
        return $this->suppliers->map(function ($supplier) {
            return [
                'Código' => $supplier->code,
                'Nombre' => $supplier->name,
                'Contacto' => $supplier->contact_name,
                'Email' => $supplier->email,
                'Teléfono' => $supplier->phone,
                'Dirección' => $supplier->address,
                'Ciudad' => $supplier->city,
                'RUC' => $supplier->ruc,
                'Estado' => $supplier->is_active ? 'Activo' : 'Inactivo',
            ];
        });
    }

    public function headings(): array
    {
        return ['Código', 'Nombre', 'Contacto', 'Email', 'Teléfono', 'Dirección', 'Ciudad', 'RUC', 'Estado'];
    }
}
