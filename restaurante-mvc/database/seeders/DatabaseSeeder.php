<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use App\Models\InventoryMovement;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Categories
        $bebidas = Category::create(['name' => 'Bebidas',   'is_active' => true, 'sort_order' => 1]);
        $comidas = Category::create(['name' => 'Comidas',   'is_active' => true, 'sort_order' => 2]);
        $postres = Category::create(['name' => 'Postres',   'is_active' => true, 'sort_order' => 3]);
        $insumos = Category::create(['name' => 'Insumos',   'is_active' => true, 'sort_order' => 4]);

        Category::create(['name' => 'Bebidas Calientes',  'parent_id' => $bebidas->id, 'is_active' => true, 'sort_order' => 1]);
        Category::create(['name' => 'Bebidas Frías',      'parent_id' => $bebidas->id, 'is_active' => true, 'sort_order' => 2]);
        Category::create(['name' => 'Entradas',           'parent_id' => $comidas->id, 'is_active' => true, 'sort_order' => 1]);
        Category::create(['name' => 'Platos Principales', 'parent_id' => $comidas->id, 'is_active' => true, 'sort_order' => 2]);

        // Products
        $products = [
            ['name' => 'Café Americano',     'sku' => 'BEB-001', 'price' => 6.50,  'cost' => 1.20,  'category_id' => $bebidas->id, 'unit' => 'portion', 'minimum_stock' => 20],
            ['name' => 'Cappuccino',          'sku' => 'BEB-002', 'price' => 8.00,  'cost' => 2.00,  'category_id' => $bebidas->id, 'unit' => 'portion', 'minimum_stock' => 15],
            ['name' => 'Agua Mineral 500ml',  'sku' => 'BEB-003', 'price' => 3.00,  'cost' => 0.80,  'category_id' => $bebidas->id, 'unit' => 'unit',    'minimum_stock' => 50],
            ['name' => 'Jugo de Naranja',     'sku' => 'BEB-004', 'price' => 7.00,  'cost' => 1.50,  'category_id' => $bebidas->id, 'unit' => 'portion', 'minimum_stock' => 10],
            ['name' => 'Hamburguesa Clásica', 'sku' => 'COM-001', 'price' => 22.00, 'cost' => 8.00,  'category_id' => $comidas->id, 'unit' => 'portion', 'minimum_stock' => 10],
            ['name' => 'Pizza Margherita',    'sku' => 'COM-002', 'price' => 35.00, 'cost' => 12.00, 'category_id' => $comidas->id, 'unit' => 'portion', 'minimum_stock' => 5],
            ['name' => 'Ensalada César',      'sku' => 'COM-003', 'price' => 18.00, 'cost' => 6.00,  'category_id' => $comidas->id, 'unit' => 'portion', 'minimum_stock' => 8],
            ['name' => 'Pastel de Chocolate', 'sku' => 'POS-001', 'price' => 12.00, 'cost' => 4.00,  'category_id' => $postres->id, 'unit' => 'portion', 'minimum_stock' => 10],
            ['name' => 'Helado Vainilla',     'sku' => 'POS-002', 'price' => 8.00,  'cost' => 2.50,  'category_id' => $postres->id, 'unit' => 'portion', 'minimum_stock' => 15],
            ['name' => 'Azúcar 1kg',          'sku' => 'INS-001', 'price' => 3.50,  'cost' => 2.00,  'category_id' => $insumos->id, 'unit' => 'kg',      'minimum_stock' => 10],
        ];

        $createdProducts = [];
        foreach ($products as $data) {
            $createdProducts[] = Product::create(array_merge($data, ['is_active' => true]));
        }

        // Branches
        $branchData = [
            ['name' => 'Sucursal Centro',     'code' => 'SUC-001', 'city' => 'Lima',  'address' => 'Av. Larco 1234',  'manager_name' => 'Juan Pérez',   'is_active' => true],
            ['name' => 'Sucursal Miraflores', 'code' => 'SUC-002', 'city' => 'Lima',  'address' => 'Calle Lima 456',  'manager_name' => 'María García', 'is_active' => true],
            ['name' => 'Sucursal San Isidro', 'code' => 'SUC-003', 'city' => 'Lima',  'address' => 'Jr. Puno 789',    'manager_name' => 'Carlos López', 'is_active' => true],
            ['name' => 'Sucursal Cusco',      'code' => 'SUC-004', 'city' => 'Cusco', 'address' => 'Plaza de Armas',  'manager_name' => 'Ana Torres',   'is_active' => true],
        ];

        $createdBranches = [];
        foreach ($branchData as $data) {
            $createdBranches[] = Branch::create($data);
        }

        // Assign products to branches with stock
        foreach ($createdBranches as $branch) {
            $pivotData = [];
            foreach ($createdProducts as $product) {
                $pivotData[$product->id] = ['stock' => rand(0, 50), 'is_available' => true];
            }
            $branch->products()->sync($pivotData);
        }

        // Users
        User::create([
            'name'      => 'Administrador',
            'email'     => 'admin@restaurantchain.com',
            'password'  => Hash::make('password'),
            'role'      => 'admin',
            'branch_id' => null,
        ]);

        User::create([
            'name'      => 'Gerente Centro',
            'email'     => 'gerente@restaurantchain.com',
            'password'  => Hash::make('password'),
            'role'      => 'manager',
            'branch_id' => $createdBranches[0]->id,
        ]);

        User::create([
            'name'      => 'Empleado Miraflores',
            'email'     => 'empleado@restaurantchain.com',
            'password'  => Hash::make('password'),
            'role'      => 'employee',
            'branch_id' => $createdBranches[1]->id,
        ]);

        // Create sample inventory movements
        $this->createInventoryMovements($createdProducts, $createdBranches);
    }

    /**
     * Create sample inventory movements
     */
    private function createInventoryMovements($products, $branches)
    {
        $adminUser = User::where('role', 'admin')->first();

        // Movements for Centro branch
        InventoryMovement::create([
            'product_id'     => $products[0]->id, // Café
            'branch_id'      => $branches[0]->id,
            'type'           => 'in',
            'quantity'       => 50,
            'previous_stock' => 0,
            'new_stock'      => 50,
            'reason'         => 'Compra semanal',
            'reference'      => 'FACT-001',
            'user_id'        => $adminUser->id,
        ]);

        InventoryMovement::create([
            'product_id'     => $products[4]->id, // Hamburguesa
            'branch_id'      => $branches[0]->id,
            'type'           => 'in',
            'quantity'       => 30,
            'previous_stock' => 0,
            'new_stock'      => 30,
            'reason'         => 'Compra de insumos',
            'reference'      => 'FACT-002',
            'user_id'        => $adminUser->id,
        ]);

        InventoryMovement::create([
            'product_id'     => $products[4]->id,
            'branch_id'      => $branches[0]->id,
            'type'           => 'out',
            'quantity'       => 15,
            'previous_stock' => 30,
            'new_stock'      => 15,
            'reason'         => 'Ventas del día',
            'reference'      => null,
            'user_id'        => $adminUser->id,
        ]);

        // Movements for Miraflores branch
        InventoryMovement::create([
            'product_id'     => $products[1]->id, // Cappuccino
            'branch_id'      => $branches[1]->id,
            'type'           => 'in',
            'quantity'       => 40,
            'previous_stock' => 0,
            'new_stock'      => 40,
            'reason'         => 'Restock',
            'reference'      => 'FACT-003',
            'user_id'        => $adminUser->id,
        ]);

        InventoryMovement::create([
            'product_id'     => $products[5]->id, // Pizza
            'branch_id'      => $branches[1]->id,
            'type'           => 'adjust',
            'quantity'       => 20,
            'previous_stock' => 25,
            'new_stock'      => 20,
            'reason'         => 'Ajuste de inventario',
            'reference'      => 'INV-001',
            'user_id'        => $adminUser->id,
        ]);
    }
}
