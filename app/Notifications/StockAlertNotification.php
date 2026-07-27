<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Messages\BroadcastMessage;

class StockAlertNotification extends Notification
{
    use Queueable;

    private $data;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($data, $status = 'low_stock')
    {
        $this->data = $data;
        $this->status = $status;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail','database','broadcast'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        $url = url(route('purchases.edit', $this->data->id));
        $title = $this->status === 'out_of_stock' ? 'The Product below is out of stock.' : 'The Product below is running low on stock.';
        $quantityText = $this->data->quantity === 0 ? 'out of stock' : $this->data->quantity . ' left in quantity';

        return (new MailMessage)
                    ->greeting('Hello!')
                    ->line($title)
                    ->line("Product's name is " . $this->data->product . " and is " . $quantityText)
                    ->line("Please update the product's quantity or make a new purchase.")
                    ->action('View Product', $url)
                    ->line('Thank you!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        $message = $this->status === 'out_of_stock'
            ? 'is out of stock.'
            : 'is low on stock.';

        return [
            'product_name' => $this->data->product,
            'quantity' => $this->data->quantity,
            'image' => $this->data->image,
            'status' => $this->status,
            'message' => $this->data->product . ' ' . $message,
        ];
    }

    /**
     * Get the broadcastable representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return BroadcastMessage
     */
    public function toBroadcast($notifiable)
    {
        return new BroadcastMessage([
            'product_name'=>$this->data->product,
            'quantity'=>$this->data->quantity,
        ]);
    }
}
