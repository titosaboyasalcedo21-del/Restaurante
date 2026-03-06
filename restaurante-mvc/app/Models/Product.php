<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class Product extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name', 'description', 'sku', 'price', 'cost',
        'category_id', 'supplier_id', 'image', 'is_active', 'minimum_stock', 'unit',
        'is_perishable', 'expiry_date', 'shelf_days',
    ];

    protected $casts = [
        'price'         => 'decimal:2',
        'cost'          => 'decimal:2',
        'is_active'     => 'boolean',
        'is_perishable' => 'boolean',
        'minimum_stock' => 'integer',
        'expiry_date'   => 'date',
        'shelf_days'    => 'integer',
    ];

    // Relationships
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function branches(): BelongsToMany
    {
        return $this->belongsToMany(Branch::class, 'branch_product')
            ->withPivot('stock', 'is_available');
    }

    public function inventoryMovements(): HasMany
    {
        return $this->hasMany(InventoryMovement::class);
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function priceHistory(): HasMany
    {
        return $this->hasMany(ProductPriceHistory::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeLowStock($query)
    {
        return $query->whereHas('branches', function ($q) {
            $q->whereRaw('branch_product.stock <= products.minimum_stock');
        });
    }

    // Accessors
    public function getProfitMarginAttribute(): ?float
    {
        if ($this->cost && $this->price > 0) {
            return round((($this->price - $this->cost) / $this->price) * 100, 2);
        }
        return null;
    }

    public function getImageUrlAttribute(): string
    {
        if ($this->image) {
            return Storage::url($this->image);
        }
        return asset('images/no-image.svg');
    }

    public function getTotalStockAttribute(): int
    {
        return $this->branches->sum('pivot.stock');
    }

    // Generate barcode SVG
    public function getBarcodeSvgAttribute(): string
    {
        $generator = new \Picqer\Barcode\BarcodeGeneratorSVG();
        return $generator->getBarcode($this->sku, \Picqer\Barcode\BarcodeGenerator::TYPE_CODE128, 2, 60);
    }

    // Record price change
    public function recordPriceChange(?string $reason = null, string $changeType = 'manual'): void
    {
        $lastPrice = $this->priceHistory()->latest()->first();

        ProductPriceHistory::create([
            'product_id' => $this->id,
            'user_id' => auth()->id(),
            'old_price' => $lastPrice?->new_price ?? $this->getOriginal('price'),
            'new_price' => $this->price,
            'old_cost' => $lastPrice?->new_cost ?? $this->getOriginal('cost'),
            'new_cost' => $this->cost,
            'change_type' => $changeType,
            'reason' => $reason,
        ]);
    }

    // Check if product is expired
    public function isExpired(): bool
    {
        if (!$this->is_perishable || !$this->expiry_date) {
            return false;
        }
        return $this->expiry_date->isPast();
    }

    // Check if product is expiring soon (within 7 days)
    public function isExpiringSoon(int $days = 7): bool
    {
        if (!$this->is_perishable || !$this->expiry_date) {
            return false;
        }
        return $this->expiry_date->lte(now()->addDays($days)) && !$this->isExpired();
    }

    // Get days until expiry
    public function getDaysUntilExpiryAttribute(): ?int
    {
        if (!$this->is_perishable || !$this->expiry_date) {
            return null;
        }
        return now()->diffInDays($this->expiry_date, false);
    }

    // Get expiry status label
    public function getExpiryStatusAttribute(): string
    {
        if (!$this->is_perishable) {
            return 'no_perishable';
        }
        if ($this->isExpired()) {
            return 'expired';
        }
        if ($this->isExpiringSoon()) {
            return 'expiring_soon';
        }
        return 'valid';
    }
}
