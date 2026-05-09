<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Setting extends Model
{
    protected $fillable = [
        'key',
        'value',
        'type',
        'description',
    ];

    /** Cache TTL in seconds (default: 1 hour). */
    private const CACHE_TTL = 3600;

    /** Prefix for all setting cache keys. */
    private const CACHE_PREFIX = 'setting_';

    /**
     * Get a setting value by key.
     * Results are cached for CACHE_TTL seconds to avoid repeated DB queries.
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        $cacheKey = self::CACHE_PREFIX . $key;

        $raw = Cache::remember($cacheKey, self::CACHE_TTL, function () use ($key) {
            return self::where('key', $key)->first();
        });

        if (!$raw) {
            return $default;
        }

        return match ($raw->type) {
            'number'  => (float) $raw->value,
            'boolean' => filter_var($raw->value, FILTER_VALIDATE_BOOLEAN),
            'json'    => json_decode($raw->value, true),
            default   => $raw->value,
        };
    }

    /**
     * Set a setting value and invalidate its cache entry.
     */
    public static function set(string $key, mixed $value, string $type = 'string'): bool
    {
        $setting = self::where('key', $key)->first();

        if (!$setting) {
            return false;
        }

        $valueToStore = match ($type) {
            'json'  => json_encode($value),
            default => $value,
        };

        $setting->update([
            'value' => $valueToStore,
            'type'  => $type,
        ]);

        // Invalidate cache so next read fetches the fresh value
        self::clearCache($key);

        return true;
    }

    /**
     * Forget the cached value for a specific key (or all settings if null).
     */
    public static function clearCache(?string $key = null): void
    {
        if ($key !== null) {
            Cache::forget(self::CACHE_PREFIX . $key);
            return;
        }

        // Flush every cached setting by iterating known keys
        $keys = self::pluck('key');
        foreach ($keys as $k) {
            Cache::forget(self::CACHE_PREFIX . $k);
        }
    }

    /**
     * Get tax rate.
     */
    public static function getTaxRate(): float
    {
        return self::get('tax_igv_rate', 18);
    }

    /**
     * Get tax name.
     */
    public static function getTaxName(): string
    {
        return self::get('tax_igv_name', 'IGV');
    }

    /**
     * Get currency symbol.
     */
    public static function getCurrencySymbol(): string
    {
        return self::get('currency_symbol', 'S/');
    }

    /**
     * Get company info.
     */
    public static function getCompanyInfo(): array
    {
        return [
            'name'    => self::get('company_name', 'Mi Restaurante'),
            'ruc'     => self::get('company_ruc', ''),
            'address' => self::get('company_address', ''),
            'phone'   => self::get('company_phone', ''),
            'email'   => self::get('company_email', ''),
        ];
    }
}
