<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Product;

class MarkExpiredProducts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'products:mark-expired';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Mark products as expired when the related purchase expiry date has passed';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $count = Product::markExpiredProducts();
        $notified = \App\Services\ExpiryNotificationService::scanAndNotifyAll();
        $this->info("Expired products updated: {$count}, Notifications dispatched: {$notified}");
        return 0;
    }
}
