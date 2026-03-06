<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ExpiryAlert extends Notification
{
    use Queueable;

    public $product;
    public $branch;
    public $expiryDate;
    public $daysUntilExpiry;

    /**
     * Create a new notification instance.
     */
    public function __construct($product, $branch, $expiryDate, $daysUntilExpiry)
    {
        $this->product = $product;
        $this->branch = $branch;
        $this->expiryDate = $expiryDate;
        $this->daysUntilExpiry = $daysUntilExpiry;
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
        $isExpired = $this->daysUntilExpiry < 0;

        $mail = (new MailMessage)
            ->subject($isExpired ? '🔴 Producto Vencido - ' . $this->product->name : '⚠️ Alerta: Producto Por Vencer - ' . $this->product->name)
            ->line('El producto **' . $this->product->name . '** en la sucursal **' . $this->branch->name . '** está ' . ($isExpired ? '**vencido**' : '**por vencer**') . '.')
            ->line('Fecha de vencimiento: **' . $this->expiryDate->format('d/m/Y') . '**')
            ->line('Días restantes: **' . $this->daysUntilExpiry . ' días**');

        if (!$isExpired) {
            $mail->action('Ver Producto', route('products.show', $this->product));
        }

        return $mail
            ->line('Por favor, tome las medidas necesarias.');
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray(object $notifiable): array
    {
        $isExpired = $this->daysUntilExpiry < 0;

        return [
            'type' => $isExpired ? 'expired' : 'expiring_soon',
            'product_id' => $this->product->id,
            'product_name' => $this->product->name,
            'branch_id' => $this->branch->id,
            'branch_name' => $this->branch->name,
            'expiry_date' => $this->expiryDate->toDateString(),
            'days_until_expiry' => $this->daysUntilExpiry,
            'message' => ($isExpired ? 'Vencido' : 'Por vencer') . ': ' . $this->product->name . ' en ' . $this->branch->name,
        ];
    }
}
