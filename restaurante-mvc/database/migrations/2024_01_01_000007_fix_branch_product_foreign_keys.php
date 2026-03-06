<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // For SQLite, we need to drop and recreate the table
        // Since this is a new app, we can just drop the table and recreate it
        // But first, let's check if we can modify the foreign keys differently

        // For SQLite with Laravel, we use raw SQL to change the foreign key behavior
        // Drop the existing foreign keys and recreate with restrictOnDelete
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

    public function down(): void
    {
        // Same process in reverse - but with cascadeOnDelete
        DB::statement('PRAGMA foreign_keys = OFF');

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

        DB::statement('PRAGMA foreign_keys = ON');
    }
};
