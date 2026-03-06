<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $fillable = [
        'key',
        'value',
        'type',
        'description',
    ];

    /**
     * Get a setting value by key
     */
    public static function get(string $key, $default = null)
    {
        $setting = self::where('key', $key)->first();

        if (!$setting) {
            return $default;
        }

        return match ($setting->type) {
            'number' => (float) $setting->value,
            'boolean' => filter_var($setting->value, FILTER_VALIDATE_BOOLEAN),
            'json' => json_decode($setting->value, true),
            default => $setting->value,
        };
    }

    /**
     * Set a setting value
     */
    public static function set(string $key, $value, string $type = 'string'): bool
    {
        $setting = self::where('key', $key)->first();

        if (!$setting) {
            return false;
        }

        $valueToStore = match ($type) {
            'json' => json_encode($value),
            default => $value,
        };

        $setting->update([
            'value' => $valueToStore,
            'type' => $type,
        ]);

        return true;
    }

    /**
     * Get tax rate
     */
    public static function getTaxRate(): float
    {
        return self::get('tax_igv_rate', 18);
    }

    /**
     * Get tax name
     */
    public static function getTaxName(): string
    {
        return self::get('tax_igv_name', 'IGV');
    }

    /**
     * Get currency symbol
     */
    public static function getCurrencySymbol(): string
    {
        return self::get('currency_symbol', 'S/');
    }

    /**
     * Get company info
     */
    public static function getCompanyInfo(): array
    {
        return [
            'name' => self::get('company_name', 'Mi Restaurante'),
            'ruc' => self::get('company_ruc', ''),
            'address' => self::get('company_address', ''),
            'phone' => self::get('company_phone', ''),
            'email' => self::get('company_email', ''),
        ];
    }
}
