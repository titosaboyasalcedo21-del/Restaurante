<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class MovementsExport implements FromCollection, WithHeadings
{
    protected $movements;

    public function __construct($movements)
    {
        $this->movements = $movements;
    }

    public function collection()
    {
        return $this->movements->map(function ($movement) {
            return [
                'Fecha' => $movement->created_at->format('d/m/Y H:i'),
                'Tipo' => $movement->type_label,
                'Producto' => $movement->product?->name,
                'SKU' => $movement->product?->sku,
                'Sucursal' => $movement->branch?->name,
                'Cantidad' => $movement->quantity,
                'Stock Anterior' => $movement->previous_stock,
                'Stock Nuevo' => $movement->new_stock,
                'Referencia' => $movement->reference,
                'Razón' => $movement->reason,
                'Usuario' => $movement->user?->name,
            ];
        });
    }

    public function headings(): array
    {
        return ['Fecha', 'Tipo', 'Producto', 'SKU', 'Sucursal', 'Cantidad', 'Stock Anterior', 'Stock Nuevo', 'Referencia', 'Razón', 'Usuario'];
    }
}
