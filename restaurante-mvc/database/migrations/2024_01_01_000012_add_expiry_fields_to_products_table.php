<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->boolean('is_perishable')->default(false)->after('is_active');
            $table->date('expiry_date')->nullable()->after('is_perishable');
            $table->integer('shelf_days')->nullable()->after('expiry_date')->comment('Días en estante antes de vencer');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['is_perishable', 'expiry_date', 'shelf_days']);
        });
    }
};
