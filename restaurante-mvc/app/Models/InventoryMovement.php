<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InventoryMovement extends Model
{
    // Type constants
    public const TYPE_IN = 'in';
    public const TYPE_OUT = 'out';
    public const TYPE_ADJUST = 'adjust';
    public const TYPE_TRANSFER = 'transfer';

    protected $fillable = [
        'product_id', 'branch_id', 'type', 'quantity',
        'previous_stock', 'new_stock', 'reason', 'reference', 'user_id',
    ];

    protected $casts = [
        'quantity'       => 'integer',
        'previous_stock' => 'integer',
        'new_stock'      => 'integer',
    ];

    // Relationships
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getTypeLabelAttribute(): string
    {
        return match($this->type) {
            'in'       => 'Entrada',
            'out'      => 'Salida',
            'adjust'   => 'Ajuste',
            'transfer' => 'Transferencia',
            default    => $this->type,
        };
    }
}
