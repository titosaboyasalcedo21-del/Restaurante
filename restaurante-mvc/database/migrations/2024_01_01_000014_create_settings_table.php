<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->string('type')->default('string'); // string, number, boolean, json
            $table->text('description')->nullable();
            $table->timestamps();
        });

        // Insert default settings
        $settings = [
            ['key' => 'tax_igv_rate', 'value' => '18', 'type' => 'number', 'description' => 'Tasa de IGV (%)'],
            ['key' => 'tax_igv_name', 'value' => 'IGV', 'type' => 'string', 'description' => 'Nombre del impuesto'],
            ['key' => 'company_name', 'value' => 'Mi Restaurante', 'type' => 'string', 'description' => 'Nombre de la empresa'],
            ['key' => 'company_ruc', 'value' => '', 'type' => 'string', 'description' => 'RUC de la empresa'],
            ['key' => 'company_address', 'value' => '', 'type' => 'string', 'description' => 'Dirección de la empresa'],
            ['key' => 'company_phone', 'value' => '', 'type' => 'string', 'description' => 'Teléfono de la empresa'],
            ['key' => 'company_email', 'value' => '', 'type' => 'string', 'description' => 'Email de la empresa'],
            ['key' => 'currency_symbol', 'value' => 'S/', 'type' => 'string', 'description' => 'Símbolo de moneda'],
            ['key' => 'currency_code', 'value' => 'PEN', 'type' => 'string', 'description' => 'Código de moneda'],
            ['key' => 'low_stock_threshold', 'value' => '5', 'type' => 'number', 'description' => 'Umbral de stock bajo por defecto'],
            ['key' => 'expiry_warning_days', 'value' => '7', 'type' => 'number', 'description' => 'Días de advertencia antes de vencer'],
        ];

        foreach ($settings as $setting) {
            \DB::table('settings')->insert($setting);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};
