<?php

namespace App\Console\Commands;

use App\Models\Setting;
use App\Models\User;
use App\Notifications\ExpiryAlert;
use App\Notifications\LowStockAlert;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class CheckInventoryAlerts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'inventory:check-alerts
                            {--dry-run : Run without sending notifications}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check inventory for low stock and expiring products, send alerts to admins';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Iniciando verificación de alertas de inventario...');

        $dryRun = $this->option('dry-run');
        if ($dryRun) {
            $this->warn('Modo simulación activado - No se enviarán notificaciones');
        }

        $lowStockCount = 0;
        $expiryCount = 0;

        // Get admins
        $admins = User::where('role', 'admin')->where('is_active', true)->get();

        if ($admins->isEmpty()) {
            $this->error('No se encontraron administradores para enviar alertas');
            return Command::FAILURE;
        }

        // Check low stock
        $this->info('Verificando productos con stock bajo...');
        $lowStockProducts = $this->checkLowStock();
        $lowStockCount = $lowStockProducts->count();

        if (!$dryRun) {
            foreach ($lowStockProducts as $item) {
                foreach ($admins as $admin) {
                    $admin->notify(new LowStockAlert(
                        $item['product'],
                        $item['branch'],
                        $item['current_stock'],
                        $item['minimum_stock']
                    ));
                }
                $this->line("  - Alerta de stock bajo: {$item['product']->name} en {$item['branch']->name}");
            }
        }

        // Check expiring products
        $this->info('Verificando productos por vencer...');
        $expiringProducts = $this->checkExpiringProducts();
        $expiryCount = $expiringProducts->count();

        if (!$dryRun) {
            foreach ($expiringProducts as $item) {
                foreach ($admins as $admin) {
                    $admin->notify(new ExpiryAlert(
                        $item['product'],
                        $item['branch'],
                        $item['expiry_date'],
                        $item['days_until_expiry']
                    ));
                }
                $status = $item['days_until_expiry'] < 0 ? 'VENCIDO' : 'por vencer';
                $this->line("  - Alerta de vencimiento ({$status}): {$item['product']->name} en {$item['branch']->name}");
            }
        }

        $this->info("Se encontraron {$lowStockCount} productos con stock bajo y {$expiryCount} productos por vencer");

        if (!$dryRun) {
            Log::info("Inventory alerts sent: {$lowStockCount} low stock, {$expiryCount} expiring");
        }

        return Command::SUCCESS;
    }

    /**
     * Check products with low stock
     */
    protected function checkLowStock()
    {
        $defaultThreshold = Setting::get('low_stock_threshold', 5);

        $lowStockItems = [];

        $branches = \App\Models\Branch::active()->with('products')->get();

        foreach ($branches as $branch) {
            foreach ($branch->products as $product) {
                $currentStock = $product->pivot->stock;
                $minimumStock = $product->minimum_stock;

                if ($currentStock <= $minimumStock) {
                    $lowStockItems[] = [
                        'product' => $product,
                        'branch' => $branch,
                        'current_stock' => $currentStock,
                        'minimum_stock' => $minimumStock,
                    ];
                }
            }
        }

        return collect($lowStockItems);
    }

    /**
     * Check products that are expiring soon or expired
     */
    protected function checkExpiringProducts()
    {
        $warningDays = Setting::get('expiry_warning_days', 7);

        $expiringItems = [];

        $branches = \App\Models\Branch::active()
            ->with(['products' => function ($query) {
                $query->where('is_perishable', true)
                      ->whereNotNull('expiry_date');
            }])
            ->get();

        foreach ($branches as $branch) {
            foreach ($branch->products as $product) {
                $expiryDate = $product->expiry_date;
                $daysUntilExpiry = now()->diffInDays($expiryDate, false);

                // Alert if expired or within warning period
                if ($daysUntilExpiry <= $warningDays) {
                    $expiringItems[] = [
                        'product' => $product,
                        'branch' => $branch,
                        'expiry_date' => $expiryDate,
                        'days_until_expiry' => $daysUntilExpiry,
                    ];
                }
            }
        }

        return collect($expiringItems);
    }
}
