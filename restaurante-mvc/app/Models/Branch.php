<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Branch extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name', 'code', 'address', 'city', 'phone',
        'email', 'manager_name', 'is_active', 'latitude', 'longitude',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'latitude'  => 'decimal:8',
        'longitude' => 'decimal:8',
    ];

    // Relationships
    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'branch_product')
            ->withPivot('stock', 'is_available');
    }

    public function inventoryMovements(): HasMany
    {
        return $this->hasMany(InventoryMovement::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    // Accessors
    public function getFullAddressAttribute(): string
    {
        return collect([$this->address, $this->city])->filter()->implode(', ');
    }

    public function getLowStockProductsAttribute()
    {
        return $this->products->filter(function ($product) {
            return $product->pivot->stock <= $product->minimum_stock;
        });
    }
}
