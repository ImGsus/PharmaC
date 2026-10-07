<?php

namespace App\Services;

use App\Models\Product;
use App\Models\Purchase;
use App\Models\User;
use App\Notifications\ProductExpiryNotification;
use Carbon\Carbon;

class ExpiryNotificationService
{
    /**
     * Threshold in days to consider a product as expiring soon (FEFO alert).
     */
    public const EXPIRING_SOON_DAYS = 30;

    /**
     * Check a purchase and its linked product.
     * Marks product as expired if applicable, and dispatches expired or expiring-soon alerts.
     *
     * @param Purchase $purchase
     * @return int Number of notifications dispatched
     */
    public static function checkAndNotifyPurchase(Purchase $purchase): int
    {
        $product = $purchase->purchaseProduct;
        $today = Carbon::today()->format('Y-m-d');
        $soonDate = Carbon::today()->addDays(self::EXPIRING_SOON_DAYS)->format('Y-m-d');
        $boxExpiries = $purchase->box_expiries;
        $hasBoxes = is_array($boxExpiries) && count($boxExpiries) > 0;

        $isExpired = false;
        $expiredBoxes = [];
        $expiringSoonBoxes = [];

        if ($hasBoxes) {
            $totalQty = (int) ($purchase->quantity ?? $purchase->total_quantity ?? 0);
            $looseQty = (int) ($purchase->item_quantity ?? 0);
            $boxCount = (int) ($purchase->packaging_box ?? count($boxExpiries));
            $qtyPerBox = $boxCount > 0 ? (int) round(($totalQty - $looseQty) / $boxCount) : 0;
            if ($qtyPerBox <= 0 && (int) ($purchase->quantity_per_box ?? 0) > 0) {
                $qtyPerBox = (int) $purchase->quantity_per_box;
            }

            foreach ($boxExpiries as $b) {
                $boxNum = $b['box'] ?? 1;
                $exp = $b['expiry_date'] ?? null;
                if ($exp) {
                    if ($exp <= $today) {
                        $isExpired = true;
                        $expiredBoxes[] = [
                            'box_num'     => $boxNum,
                            'label'       => 'Package#' . $boxNum,
                            'expiry_date' => $exp,
                            'quantity'    => $qtyPerBox,
                        ];
                    } elseif ($exp <= $soonDate) {
                        $daysLeft = (int) Carbon::today()->diffInDays(Carbon::parse($exp));
                        $expiringSoonBoxes[] = [
                            'box_num'     => $boxNum,
                            'label'       => 'Package#' . $boxNum,
                            'expiry_date' => $exp,
                            'quantity'    => $qtyPerBox,
                            'days_left'   => $daysLeft,
                        ];
                    }
                }
            }
        }

        // Also check purchase overall expiry_date
        $isOverallExpired = false;
        $isOverallExpiringSoon = false;
        $overallDaysLeft = null;

        if ($purchase->expiry_date) {
            if ($purchase->expiry_date <= $today) {
                $isExpired = true;
                $isOverallExpired = true;
            } elseif ($purchase->expiry_date <= $soonDate && empty($expiringSoonBoxes)) {
                $isOverallExpiringSoon = true;
                $overallDaysLeft = (int) Carbon::today()->diffInDays(Carbon::parse($purchase->expiry_date));
            }
        }

        if ($product) {
            if ($isExpired && !$product->expired) {
                $product->expired = true;
                $product->save();
            }
        }

        $isActive = $product ? (bool) $product->is_active : true;
        if (!$isActive) {
            return 0;
        }

        $users = User::all();
        $notifiedCount = 0;

        // 1. Dispatch Expired Notifications
        if (!empty($expiredBoxes)) {
            foreach ($expiredBoxes as $box) {
                $payload = [
                    'type'          => 'expired',
                    'product_id'    => $product ? $product->id : null,
                    'purchase_id'   => $purchase->id,
                    'product_name'  => $purchase->product,
                    'package_label' => $box['label'],
                    'quantity'      => $box['quantity'],
                    'image'         => $purchase->image,
                    'is_active'     => $isActive,
                    'expiry_date'   => $box['expiry_date'],
                    'message'       => $purchase->product . ' - ' . $box['label'] . ' is expired (' . $box['quantity'] . ' units) and currently Active. Action required!',
                    'action_url'    => route('expired'),
                ];

                foreach ($users as $user) {
                    $alreadyUnread = $user->unreadNotifications()
                        ->where('type', ProductExpiryNotification::class)
                        ->where('data->purchase_id', $purchase->id)
                        ->where('data->type', 'expired')
                        ->where('data->package_label', $box['label'])
                        ->exists();

                    if (!$alreadyUnread) {
                        $user->notify(new ProductExpiryNotification($payload));
                        $notifiedCount++;
                    }
                }
            }
        } elseif ($isOverallExpired) {
            $payload = [
                'type'          => 'expired',
                'product_id'    => $product ? $product->id : null,
                'purchase_id'   => $purchase->id,
                'product_name'  => $purchase->product,
                'package_label' => null,
                'quantity'      => (int) ($purchase->quantity ?? 0),
                'image'         => $purchase->image,
                'is_active'     => $isActive,
                'expiry_date'   => $purchase->expiry_date,
                'message'       => $purchase->product . ' is expired (' . (int) $purchase->quantity . ' units) and currently Active. Action required!',
                'action_url'    => route('expired'),
            ];

            foreach ($users as $user) {
                $alreadyUnread = $user->unreadNotifications()
                    ->where('type', ProductExpiryNotification::class)
                    ->where('data->purchase_id', $purchase->id)
                    ->where('data->type', 'expired')
                    ->whereNull('data->package_label')
                    ->exists();

                if (!$alreadyUnread) {
                    $user->notify(new ProductExpiryNotification($payload));
                    $notifiedCount++;
                }
            }
        }

        // 2. Dispatch Expiring Soon (FEFO) Notifications
        if (!empty($expiringSoonBoxes)) {
            foreach ($expiringSoonBoxes as $box) {
                $payload = [
                    'type'          => 'expiring_soon',
                    'product_id'    => $product ? $product->id : null,
                    'purchase_id'   => $purchase->id,
                    'product_name'  => $purchase->product,
                    'package_label' => $box['label'],
                    'quantity'      => $box['quantity'],
                    'days_left'     => $box['days_left'],
                    'image'         => $purchase->image,
                    'is_active'     => $isActive,
                    'expiry_date'   => $box['expiry_date'],
                    'message'       => $purchase->product . ' - ' . $box['label'] . ' will expire in ' . $box['days_left'] . ' days (' . $box['expiry_date'] . '). Prioritize dispensing!',
                    'action_url'    => route('products.index'),
                ];

                foreach ($users as $user) {
                    $alreadyUnread = $user->unreadNotifications()
                        ->where('type', ProductExpiryNotification::class)
                        ->where('data->purchase_id', $purchase->id)
                        ->where('data->type', 'expiring_soon')
                        ->where('data->package_label', $box['label'])
                        ->exists();

                    if (!$alreadyUnread) {
                        $user->notify(new ProductExpiryNotification($payload));
                        $notifiedCount++;
                    }
                }
            }
        } elseif ($isOverallExpiringSoon) {
            $payload = [
                'type'          => 'expiring_soon',
                'product_id'    => $product ? $product->id : null,
                'purchase_id'   => $purchase->id,
                'product_name'  => $purchase->product,
                'package_label' => null,
                'quantity'      => (int) ($purchase->quantity ?? 0),
                'days_left'     => $overallDaysLeft,
                'image'         => $purchase->image,
                'is_active'     => $isActive,
                'expiry_date'   => $purchase->expiry_date,
                'message'       => $purchase->product . ' will expire in ' . $overallDaysLeft . ' days (' . $purchase->expiry_date . '). Prioritize dispensing!',
                'action_url'    => route('products.index'),
            ];

            foreach ($users as $user) {
                $alreadyUnread = $user->unreadNotifications()
                    ->where('type', ProductExpiryNotification::class)
                    ->where('data->purchase_id', $purchase->id)
                    ->where('data->type', 'expiring_soon')
                    ->whereNull('data->package_label')
                    ->exists();

                if (!$alreadyUnread) {
                    $user->notify(new ProductExpiryNotification($payload));
                    $notifiedCount++;
                }
            }
        }

        return $notifiedCount;
    }

    /**
     * Check a product instance directly.
     *
     * @param Product $product
     * @return int
     */
    public static function checkAndNotifyProduct(Product $product): int
    {
        if ($product->purchase) {
            return self::checkAndNotifyPurchase($product->purchase);
        }
        return 0;
    }

    /**
     * Scan all purchases and products, marking expired and dispatching unread notifications.
     *
     * @return int Total notifications dispatched
     */
    public static function scanAndNotifyAll(): int
    {
        $purchases = Purchase::with('purchaseProduct')->get();
        $total = 0;
        foreach ($purchases as $p) {
            $total += self::checkAndNotifyPurchase($p);
        }
        return $total;
    }
}
