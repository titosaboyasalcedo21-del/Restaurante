<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class PurchaseOrdersExport implements FromCollection, WithHeadings
{
    protected $orders;

    public function __construct($orders)
    {
        $this->orders = $orders;
    }

    public function collection()
    {
        return $this->orders->map(function ($order) {
            return [
                'Número' => $order->order_number,
                'Fecha' => $order->order_date->format('d/m/Y'),
                'Proveedor' => $order->supplier?->name,
                'Sucursal' => $order->branch?->name,
                'Estado' => $order->status_label,
                'Subtotal' => $order->subtotal,
                'IGV' => $order->tax,
                'Total' => $order->total,
                'Fecha Esperada' => $order->expected_date?->format('d/m/Y'),
                'Fecha Recepción' => $order->received_date?->format('d/m/Y'),
                'Usuario' => $order->user?->name,
                'Notas' => $order->notes,
            ];
        });
    }

    public function headings(): array
    {
        return ['Número', 'Fecha', 'Proveedor', 'Sucursal', 'Estado', 'Subtotal', 'IGV', 'Total', 'Fecha Esperada', 'Fecha Recepción', 'Usuario', 'Notas'];
    }
}
