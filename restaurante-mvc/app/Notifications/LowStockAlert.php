<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class LowStockAlert extends Notification
{
    use Queueable;

    public $product;
    public $branch;
    public $currentStock;
    public $minimumStock;

    /**
     * Create a new notification instance.
     */
    public function __construct($product, $branch, $currentStock, $minimumStock)
    {
        $this->product = $product;
        $this->branch = $branch;
        $this->currentStock = $currentStock;
        $this->minimumStock = $minimumStock;
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('⚠️ Alerta: Stock Bajo - ' . $this->product->name)
            ->line('El producto **' . $this->product->name . '** en la sucursal **' . $this->branch->name . '** tiene stock bajo.')
            ->line('Stock actual: **' . $this->currentStock . '**')
            ->line('Stock mínimo: **' . $this->minimumStock . '**')
            ->action('Ver Producto', route('products.show', $this->product))
            ->line('Por favor, considere crear una orden de compra para reponer el inventario.');
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'low_stock',
            'product_id' => $this->product->id,
            'product_name' => $this->product->name,
            'branch_id' => $this->branch->id,
            'branch_name' => $this->branch->name,
            'current_stock' => $this->currentStock,
            'minimum_stock' => $this->minimumStock,
            'message' => 'Stock bajo: ' . $this->product->name . ' en ' . $this->branch->name,
        ];
    }
}
