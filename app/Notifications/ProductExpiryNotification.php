<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class ProductExpiryNotification extends Notification
{
    use Queueable;

    public $data;

    /**
     * Create a new notification instance.
     *
     * @param array $data
     */
    public function __construct(array $data)
    {
        $this->data = $data;
    }

    /**
     * Delivery channels: database only (fast, non-blocking, reliable).
     *
     * @param mixed $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['database'];
    }

    /**
     * Database notification payload.
     *
     * @param mixed $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            'type'          => $this->data['type'] ?? 'expired',
            'product_id'    => $this->data['product_id'] ?? null,
            'purchase_id'   => $this->data['purchase_id'] ?? null,
            'product_name'  => $this->data['product_name'] ?? 'Product',
            'package_label' => $this->data['package_label'] ?? null,
            'quantity'      => $this->data['quantity'] ?? 0,
            'days_left'     => $this->data['days_left'] ?? null,
            'image'         => $this->data['image'] ?? null,
            'status'        => $this->data['status'] ?? ($this->data['type'] ?? 'expired'),
            'is_active'     => $this->data['is_active'] ?? true,
            'expiry_date'   => $this->data['expiry_date'] ?? null,
            'message'       => $this->data['message'] ?? 'Product alert. Needs action!',
            'action_url'    => $this->data['action_url'] ?? (isset($this->data['type']) && $this->data['type'] === 'expiring_soon' ? route('products.index') : route('expired')),
        ];
    }
}

