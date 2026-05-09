<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $driver = DB::getDriverName();

        if ($driver === 'mysql') {
            // In MySQL we can just drop and add the constraints
            Schema::table('branch_product', function (Blueprint $table) {
                $table->dropForeign(['branch_id']);
                $table->dropForeign(['product_id']);

                $table->foreign('branch_id')
                    ->references('id')->on('branches')
                    ->onDelete('restrict');
                $table->foreign('product_id')
                    ->references('id')->on('products')
                    ->onDelete('restrict');
            });
            return;
        }

        if ($driver === 'sqlite') {
            DB::statement('PRAGMA foreign_keys = OFF');
            
            // Rename the table temporarily
            Schema::rename('branch_product', 'branch_product_old');

            // Create new table with correct constraints
            Schema::create('branch_product', function (Blueprint $table) {
                $table->foreignId('branch_id')
                    ->constrained()
                    ->restrictOnDelete();
                $table->foreignId('product_id')
                    ->constrained()
                    ->restrictOnDelete();
                $table->integer('stock')->default(0);
                $table->boolean('is_available')->default(true);
                $table->primary(['branch_id', 'product_id']);
            });

            // Copy data from old table
            DB::statement('INSERT INTO branch_product (branch_id, product_id, stock, is_available)
                SELECT branch_id, product_id, stock, is_available FROM branch_product_old');

            // Drop old table
            Schema::dropIfExists('branch_product_old');
            DB::statement('PRAGMA foreign_keys = ON');
        }
    }

    public function down(): void
    {
        $driver = DB::getDriverName();
        if ($driver === 'sqlite') {
            DB::statement('PRAGMA foreign_keys = OFF');
        } elseif ($driver === 'mysql') {
            DB::statement('SET FOREIGN_KEY_CHECKS = 0');
        }

        Schema::rename('branch_product', 'branch_product_old');

        Schema::create('branch_product', function (Blueprint $table) {
            $table->foreignId('branch_id')
                ->constrained()
                ->cascadeOnDelete();
            $table->foreignId('product_id')
                ->constrained()
                ->cascadeOnDelete();
            $table->integer('stock')->default(0);
            $table->boolean('is_available')->default(true);
            $table->primary(['branch_id', 'product_id']);
        });

        DB::statement('INSERT INTO branch_product (branch_id, product_id, stock, is_available)
            SELECT branch_id, product_id, stock, is_available FROM branch_product_old');

        Schema::dropIfExists('branch_product_old');

        if ($driver === 'sqlite') {
            DB::statement('PRAGMA foreign_keys = ON');
        } elseif ($driver === 'mysql') {
            DB::statement('SET FOREIGN_KEY_CHECKS = 1');
        }
    }
};
