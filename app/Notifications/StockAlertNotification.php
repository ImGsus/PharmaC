<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Messages\BroadcastMessage;

/**
 * Stock-low / out-of-stock alert sent to every user.
 *
 * This notification runs through the queue (ShouldQueue) so it never
 * blocks the request that triggered it — i.e. the POS Save click. The
 * previous version shipped a real SMTP email inside the Sales
 * transaction, which made Save hang for the full SMTP timeout when the
 * mail server was slow or unreachable, and turned the Stock Alert into
 * a hot-button bug for cashiers who couldn't see their receipt toast.
 *
 * Even with `ShouldQueue`, when the queue driver is `sync` (the default
 * in `.env`) the job still runs inline, so the `mail` channel was
 * dropped from `via()` for the time being. The notification now writes
 * a `database` row (visible on the bell-icon dropdown) and a `broadcast`
 * event for any subscribed websockets. Re-add `mail` once a real queue
 * worker is running.
 */
class StockAlertNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Notification payload.
     *
     * `$data` carries the purchase row (or whatever the listener passes),
     * and `$status` is one of `low_stock` | `out_of_stock`. Both must be
     * declared as class properties — PHP 8.2 promotes dynamic-property
     * creation to E_DEPRECATED, and with Laravel's ErrorHandler turning
     * deprecations into ErrorException, the queued-toMail path crashes
     * with "Undefined property" the moment the notification tries to
     * serialize `started_at` for queue storage.
     */
    public $data;
    public $status;

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
     * `mail` is intentionally *not* in this list: when the queue driver is
     * `sync` (the default in `.env`), `ShouldQueue` jobs still run inline,
     * and a synchronous SMTP send inside the POS Save click was the
     * root cause of "Save takes very long to progress a purchase" — the
     * SMTP server (or its absence) would block the request for its full
     * timeout. The stock alert is purely an internal notification now;
     * admins who want email can wire `mail` back in once a real queue
     * worker is running.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['database','broadcast'];
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
