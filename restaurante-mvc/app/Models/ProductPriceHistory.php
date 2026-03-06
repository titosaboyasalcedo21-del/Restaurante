<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductPriceHistory extends Model
{
    protected $table = 'product_price_history';

    protected $fillable = [
        'product_id', 'user_id', 'old_price', 'new_price',
        'old_cost', 'new_cost', 'change_type', 'reason',
    ];

    protected $casts = [
        'old_price' => 'decimal:2',
        'new_price' => 'decimal:2',
        'old_cost' => 'decimal:2',
        'new_cost' => 'decimal:2',
    ];

    // Relationships
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // Accessors
    public function getPriceChangeAttribute(): ?float
    {
        if ($this->old_price && $this->new_price) {
            return $this->new_price - $this->old_price;
        }
        return null;
    }

    public function getPriceChangePercentAttribute(): ?float
    {
        if ($this->old_price && $this->old_price > 0) {
            return round((($this->new_price - $this->old_price) / $this->old_price) * 100, 2);
        }
        return null;
    }

    public function getCostChangeAttribute(): ?float
    {
        if ($this->old_cost && $this->new_cost) {
            return $this->new_cost - $this->old_cost;
        }
        return null;
    }
}
