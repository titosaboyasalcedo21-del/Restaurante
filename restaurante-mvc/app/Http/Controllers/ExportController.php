<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;
use App\Models\Branch;
use App\Models\Supplier;
use App\Models\InventoryMovement;
use App\Models\PurchaseOrder;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class ExportController extends Controller
{
    // ========== EXPORT EXCEL ==========

    public function exportProducts(Request $request)
    {
        $products = Product::with('category', 'supplier')
            ->when($request->filled('category_id'), fn($q) => $q->where('category_id', $request->category_id))
            ->when($request->filled('status'), fn($q) => $q->where('is_active', $request->status === 'active'))
            ->orderBy('name')
            ->get();

        return Excel::download(new \App\Exports\ProductsExport($products), 'productos_' . date('Y-m-d') . '.xlsx');
    }

    public function exportCategories()
    {
        $categories = Category::with('parent')->orderBy('name')->get();
        return Excel::download(new \App\Exports\CategoriesExport($categories), 'categorias_' . date('Y-m-d') . '.xlsx');
    }

    public function exportSuppliers()
    {
        $suppliers = Supplier::orderBy('name')->get();
        return Excel::download(new \App\Exports\SuppliersExport($suppliers), 'proveedores_' . date('Y-m-d') . '.xlsx');
    }

    public function exportBranches()
    {
        $branches = Branch::withCount('products')->orderBy('name')->get();
        return Excel::download(new \App\Exports\BranchesExport($branches), 'sucursales_' . date('Y-m-d') . '.xlsx');
    }

    public function exportInventory(Request $request)
    {
        $branchId = $request->get('branch_id');

        $branches = Branch::with(['products' => function ($q) use ($branchId) {
            if ($branchId) {
                $q->where('branches.id', $branchId);
            }
            $q->with('category');
        }])->when($branchId, fn($q) => $q->where('id', $branchId))
          ->get();

        return Excel::download(new \App\Exports\InventoryExport($branches), 'inventario_' . date('Y-m-d') . '.xlsx');
    }

    public function exportMovements(Request $request)
    {
        $movements = InventoryMovement::with('product', 'branch', 'user')
            ->when($request->filled('branch_id'), fn($q) => $q->where('branch_id', $request->branch_id))
            ->when($request->filled('type'), fn($q) => $q->where('type', $request->type))
            ->when($request->filled('date_from'), fn($q) => $q->whereDate('created_at', '>=', $request->date_from))
            ->when($request->filled('date_to'), fn($q) => $q->whereDate('created_at', '<=', $request->date_to))
            ->orderByDesc('created_at')
            ->get();

        return Excel::download(new \App\Exports\MovementsExport($movements), 'movimientos_' . date('Y-m-d') . '.xlsx');
    }

    public function exportPurchaseOrders(Request $request)
    {
        $orders = PurchaseOrder::with('supplier', 'branch', 'user')
            ->when($request->filled('status'), fn($q) => $q->where('status', $request->status))
            ->when($request->filled('date_from'), fn($q) => $q->whereDate('order_date', '>=', $request->date_from))
            ->when($request->filled('date_to'), fn($q) => $q->whereDate('order_date', '<=', $request->date_to))
            ->orderByDesc('created_at')
            ->get();

        return Excel::download(new \App\Exports\PurchaseOrdersExport($orders), 'ordenes_compra_' . date('Y-m-d') . '.xlsx');
    }

    // ========== EXPORT PDF ==========

    public function pdfProduct(Product $product)
    {
        $product->load('category', 'supplier', 'branches');

        $pdf = Pdf::loadView('exports.product-pdf', compact('product'));
        return $pdf->download('producto_' . $product->sku . '.pdf');
    }

    public function pdfInventory(Request $request)
    {
        $branchId = $request->get('branch_id');

        $branches = Branch::with(['products' => function ($q) {
            $q->with('category')->orderBy('products.name');
        }])->when($branchId, fn($q) => $q->where('id', $branchId))
          ->orderBy('name')
          ->get();

        $pdf = Pdf::loadView('exports.inventory-pdf', compact('branches', 'branchId'));
        return $pdf->download('inventario_' . date('Y-m-d') . '.pdf');
    }

    public function pdfMovementReport(Request $request)
    {
        $dateFrom = $request->get('date_from', now()->startOfMonth()->toDateString());
        $dateTo = $request->get('date_to', now()->toDateString());

        $movements = InventoryMovement::with('product', 'branch', 'user')
            ->whereDate('created_at', '>=', $dateFrom)
            ->whereDate('created_at', '<=', $dateTo)
            ->when($request->filled('branch_id'), fn($q) => $q->where('branch_id', $request->branch_id))
            ->orderByDesc('created_at')
            ->get();

        $summary = $movements->groupBy('type')->map(fn($g) => [
            'count' => $g->count(),
            'total_quantity' => $g->sum('quantity'),
        ]);

        $pdf = Pdf::loadView('exports.movements-pdf', compact('movements', 'summary', 'dateFrom', 'dateTo'));
        return $pdf->download('reporte_movimientos_' . $dateFrom . '_' . $dateTo . '.pdf');
    }

    public function pdfPurchaseOrder(PurchaseOrder $purchaseOrder)
    {
        $purchaseOrder->load('supplier', 'branch', 'user', 'items.product');

        $pdf = Pdf::loadView('exports.purchase-order-pdf', compact('purchaseOrder'));
        return $pdf->download('orden_' . $purchaseOrder->order_number . '.pdf');
    }

    // ========== IMPORT ==========

    public function importProducts()
    {
        return view('imports.products');
    }

    public function processProductsImport(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv|max:10240',
        ]);

        $import = new \App\Imports\ProductsImport();

        try {
            Excel::import($import, $request->file('file'));

            $imported = $import->getImportedCount();
            $errors = $import->getErrors();

            if (empty($errors)) {
                return redirect()->route('products.index')->with('success', "{$imported} productos importados exitosamente.");
            } else {
                return back()->with('warning', "{$imported} productos importados. Errores: " . implode(', ', $errors));
            }
        } catch (\Maatwebsite\Excel\Validators\ValidationException $e) {
            $failures = $e->failures();
            $errors = [];

            foreach ($failures as $failure) {
                $errors[] = "Fila {$failure->row()}: " . implode(', ', $failure->errors());
            }

            return back()->with('error', implode('<br>', $errors));
        }
    }
}
