<?php

namespace App\Imports;

use App\Models\Product;
use App\Models\Category;
use App\Models\Supplier;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Illuminate\Validation\Rule;

class ProductsImport implements ToModel, WithHeadingRow, WithValidation
{
    protected $importedCount = 0;
    protected $errors = [];

    public function model(array $row)
    {
        // Find category by name
        $categoryId = null;
        if (!empty($row['categoria'])) {
            $category = Category::where('name', $row['categoria'])->first();
            $categoryId = $category?->id;
        }

        // Find supplier by name
        $supplierId = null;
        if (!empty($row['proveedor'])) {
            $supplier = Supplier::where('name', $row['proveedor'])->first();
            $supplierId = $supplier?->id;
        }

        // Check if product exists by SKU
        $product = Product::where('sku', $row['sku'] ?? $row['codigo'])->first();

        if ($product) {
            // Update existing product
            $product->update([
                'name' => $row['nombre'] ?? $product->name,
                'description' => $row['descripcion'] ?? $product->description,
                'price' => $row['precio'] ?? $product->price,
                'cost' => $row['costo'] ?? $product->cost,
                'category_id' => $categoryId ?? $product->category_id,
                'supplier_id' => $supplierId ?? $product->supplier_id,
                'minimum_stock' => $row['stock_minimo'] ?? $product->minimum_stock,
                'unit' => $row['unidad'] ?? $product->unit,
            ]);

            // Record price history if price changed
            if (isset($row['precio']) && $row['precio'] != $product->getOriginal('price')) {
                $product->recordPriceChange('Importación masiva', 'import');
            }
        } else {
            // Create new product
            $product = Product::create([
                'name' => $row['nombre'] ?? $row['name'] ?? 'Producto sin nombre',
                'sku' => $row['sku'] ?? $row['codigo'] ?? 'SKU-' . time() . '-' . rand(1000, 9999),
                'description' => $row['descripcion'] ?? $row['description'] ?? null,
                'price' => $row['precio'] ?? $row['price'] ?? 0,
                'cost' => $row['costo'] ?? $row['cost'] ?? 0,
                'category_id' => $categoryId,
                'supplier_id' => $supplierId,
                'minimum_stock' => $row['stock_minimo'] ?? $row['minimum_stock'] ?? 0,
                'unit' => $row['unidad'] ?? $row['unit'] ?? 'und',
                'is_active' => true,
            ]);

            // Record initial price
            if ($product->price > 0) {
                $product->recordPriceChange('Creado por importación', 'import');
            }
        }

        $this->importedCount++;

        return $product;
    }

    public function rules(): array
    {
        return [
            'nombre' => 'required|string|max:255',
            'sku' => 'nullable|string|max:100',
            'precio' => 'nullable|numeric|min:0',
            'costo' => 'nullable|numeric|min:0',
            'categoria' => 'nullable|string|max:255',
            'proveedor' => 'nullable|string|max:255',
            'stock_minimo' => 'nullable|integer|min:0',
            'unidad' => 'nullable|string|max:50',
        ];
    }

    public function getImportedCount(): int
    {
        return $this->importedCount;
    }

    public function getErrors(): array
    {
        return $this->errors;
    }
}
