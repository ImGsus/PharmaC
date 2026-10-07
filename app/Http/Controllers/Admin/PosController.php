<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\PosSession;
use App\Models\Sale;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PosController extends Controller
{
    /**
     * Display the POS / Cashier orders page.
     *
     * Loads products (with their related purchase) so the "Select Item"
     * dropdown can show product name + price + current stock.
     */
    public function index()
    {
        $title = 'pos orders';

        $products = Product::with(['purchase.category'])
            ->where('is_active', true)
            ->whereHas('purchase', function ($q) {
                // Only show products that still have stock available.
                $q->where('quantity', '>', 0);
            })
            ->whereNull('deleted_at')
            ->get()
            // Sort by the human-readable product name from the related purchase.
            ->sortBy(function ($product) {
                return optional($product->purchase)->product ?? '';
            })
            // Then put expired items at the bottom so fresh stock is at the top.
            ->sortBy(function ($product) {
                return $product->expired ? 1 : 0;
            })
            // Shape into a flat array with only what the tile grid needs.
            ->map(function (Product $product) {
                $purchase = $product->purchase;
                $boxCount = (int) (optional($purchase)->packaging_box ?? 0);
                $looseQty = (int) (optional($purchase)->item_quantity ?? 0);
                $totalQty = (int) (optional($purchase)->quantity ?? 0);
                $qtyPerBox = (int) (optional($purchase)->quantity_per_box ?? 0);
                if ($boxCount > 0 && ($qtyPerBox <= 0 || ($qtyPerBox * $boxCount + $looseQty !== $totalQty && $totalQty > $looseQty))) {
                    $calc = (int) round(($totalQty - $looseQty) / $boxCount);
                    if ($calc > 0) {
                        $qtyPerBox = $calc;
                    }
                }

                return [
                    'id'          => $product->id,
                    'name'        => optional($purchase)->product ?? 'Unnamed product',
                    'price'       => (float) ($product->price ?? 0),
                    'stock'       => $totalQty,
                    'category_id' => optional(optional($purchase)->category)->id,
                    'category'    => optional(optional($purchase)->category)->name ?? 'Uncategorized',
                    'image'       => !empty(optional($purchase)->image)
                        ? optional($purchase)->image_url
                        : asset('assets/img/productnoimage.png'),
                    'expired'          => (bool) $product->expired,
                    'packaging_box'    => $boxCount,
                    'quantity_per_box' => $qtyPerBox,
                    'item_quantity'    => $looseQty,
                    'box_expiries'     => optional($purchase)->box_expiries ?? [],
                ];
            })
            ->values()
            ->all();

        $categories = \App\Models\Category::orderBy('name')->get(['id', 'name']);

        return view('admin.pos.orders', compact('title', 'products', 'categories'));
    }

    /**
     * JSON: history of recent sales (optionally filtered by customer name).
     * Used by the History modal on the POS page.
     */
    public function history(Request $request)
    {
        $customer = trim((string) $request->query('customer', ''));

        $query = Sale::with('product.purchase')
            ->orderByDesc('created_at')
            ->limit(50);

        if ($customer !== '') {
            // After migration, sales has a customer_name column. Older rows
            // (pre-migration) won't match — that's expected.
            $query->where('customer_name', $customer);
        }

        $sales = $query->get()->map(function (Sale $sale) {
            return [
                'id'              => $sale->id,
                'product_name'    => optional(optional($sale->product)->purchase)->product ?? 'N/A',
                'quantity'        => (int) $sale->quantity,
                'total_price'     => (float) $sale->total_price,
                'payment_method'  => $sale->payment_method,
                'customer_name'   => $sale->customer_name,
                'created_at'      => optional($sale->created_at)->format('Y-m-d H:i'),
            ];
        });

        return response()->json([
            'customer' => $customer,
            'count'    => $sales->count(),
            'sales'    => $sales,
        ]);
    }

    /**
     * JSON: list of saved orders, one row per cart (one Save click).
     * Used by the Print-orders modal so the cashier can pick which receipt
     * to print. Rows are grouped by the same signature the receipt()
     * method uses: customer_name + payment_method + payment_amount + minute.
     */
    public function ordersList(Request $request)
    {
        $customer = trim((string) $request->query('customer', ''));

        $query = Sale::with('product.purchase')
            ->orderByDesc('id')
            ->limit(200);

        if ($customer !== '') {
            $query->where('customer_name', $customer);
        }

        $rows = $query->get();

        // Group sibling rows into one "order" using the same signature as
        // receipt(): customer + payment_method + payment_amount + minute.
        $groups = [];
        foreach ($rows as $row) {
            $key = (
                ($row->customer_name ?? '') . '|' .
                ($row->payment_method ?? '') . '|' .
                ($row->payment_amount ?? '') . '|' .
                ($row->discount ?? 0) . '|' .
                ($row->created_at ? $row->created_at->format('Y-m-d H:i') : '')
            );
            if (!isset($groups[$key])) {
                $groups[$key] = [
                    'first_id'       => $row->id,
                    'created_at'     => $row->created_at,
                    'customer_name'  => $row->customer_name,
                    'payment_method' => $row->payment_method,
                    'payment_amount' => $row->payment_amount,
                    'change_amount'  => $row->change_amount,
                    'discount'       => $row->discount,
                    'total'          => 0.0,
                    'item_count'     => 0,
                    'items'          => [],
                ];
            }
            $groups[$key]['total']      += (float) $row->total_price;
            $groups[$key]['item_count'] += (int) $row->quantity;
            $groups[$key]['items'][]     = [
                'name'       => optional(optional($row->product)->purchase)->product ?? 'N/A',
                'category'   => optional(optional(optional($row->product)->purchase)->category)->name ?? 'Uncategorized',
                'qty'        => (int) $row->quantity,
                'unit_price' => $row->quantity ? round((float) $row->total_price / $row->quantity, 2) : 0.0,
                'line'       => (float) $row->total_price,
            ];
        }

        // Newest first
        $orders = collect($groups)->sortByDesc(function ($g) {
            return $g['created_at'] ? $g['created_at']->timestamp : 0;
        })->values()->take(50)->map(function ($g) {
            return [
                'id'             => $g['first_id'],
                'created_at'     => $g['created_at'] ? $g['created_at']->format('Y-m-d H:i') : null,
                'customer_name'  => $g['customer_name'] ?? 'Walk-in customer',
                'payment_method' => $g['payment_method'] ?? 'cash',
                'total'          => round($g['total'], 2),
                'discount'       => (float) ($g['discount'] ?? 0),
                'payment_amount' => (float) ($g['payment_amount'] ?? 0),
                'change_amount'  => (float) ($g['change_amount'] ?? 0),
                'item_count'     => $g['item_count'],
                'items'          => $g['items'],
            ];
        })->values();

        return response()->json([
            'customer' => $customer,
            'count'    => $orders->count(),
            'orders'   => $orders,
        ]);
    }

    /**
     * JSON: sales report (income) for a given period.
     * Used by the Report modal on the POS page.
     */
    public function report(Request $request)
    {
        $period = $request->query('period', 'daily');
        $today  = Carbon::today();

        switch ($period) {
            case 'weekly':
                $start = $today->copy()->subDays(6); // last 7 days incl. today
                $label = 'Last 7 days';
                break;
            case 'monthly':
                $start = $today->copy()->subDays(29); // last 30 days incl. today
                $label = 'Last 30 days';
                break;
            case 'yearly':
                $start = $today->copy()->subDays(364); // last 365 days incl. today
                $label = 'Last 365 days';
                break;
            case 'daily':
            default:
                $start = $today->copy();
                $label = 'Today';
                $period = 'daily';
                break;
        }

        $end = $today->copy()->endOfDay();

        $sales = Sale::with('product.purchase')
            ->whereBetween('created_at', [$start, $end])
            ->orderByDesc('created_at')
            ->get();

        $income = (float) $sales->sum('total_price');

        return response()->json([
            'period'  => $period,
            'label'   => $label,
            'start'   => $start->toDateString(),
            'end'     => $end->toDateString(),
            'count'   => $sales->count(),
            'income'  => $income,
            'sales'   => $sales->map(function (Sale $sale) {
                return [
                    'id'           => $sale->id,
                    'product_name' => optional(optional($sale->product)->purchase)->product ?? 'N/A',
                    'quantity'     => (int) $sale->quantity,
                    'total_price'  => (float) $sale->total_price,
                    'created_at'   => optional($sale->created_at)->format('Y-m-d H:i'),
                ];
            }),
        ]);
    }

    /**
     * JSON: look up a product by its barcode (SKU).
     *
    * Used by the POS onScan handler to resolve a detected scanner code to
    * its connected product before adding it to the cart.
     */
    public function scan(Request $request)
    {
        $barcode = trim((string) $request->input('barcode', ''));

        if ($barcode === '') {
            return response()->json([
                'ok'      => false,
                'message' => 'Empty barcode.',
            ], 422);
        }

        $product = Product::with('purchase')
            ->where('barcode', $barcode)
            ->whereNull('deleted_at')
            ->where('is_active', true)
            ->first();

        if (!$product) {
            return response()->json([
                'ok'      => false,
                'message' => 'No product found for barcode "' . $barcode . '".',
            ], 404);
        }

        $purchase = $product->purchase;
        $totalQty = (int) (optional($purchase)->quantity ?? 0);
        $boxCount = (int) (optional($purchase)->packaging_box ?? 0);
        $looseQty = (int) (optional($purchase)->item_quantity ?? 0);
        $qtyPerBox = (int) (optional($purchase)->quantity_per_box ?? 0);
        if ($boxCount > 0 && ($qtyPerBox <= 0 || ($qtyPerBox * $boxCount + $looseQty !== $totalQty && $totalQty > $looseQty))) {
            $calc = (int) round(($totalQty - $looseQty) / $boxCount);
            if ($calc > 0) {
                $qtyPerBox = $calc;
            }
        }

        return response()->json([
            'ok'      => true,
            'product' => [
                'id'               => $product->id,
                'name'             => optional($purchase)->product ?? 'Unnamed product',
                'price'            => (float) ($product->price ?? 0),
                'stock'            => $totalQty,
                'expired'          => (bool) $product->expired,
                'packaging_box'    => $boxCount,
                'quantity_per_box' => $qtyPerBox,
                'item_quantity'    => $looseQty,
                'box_expiries'     => optional($purchase)->box_expiries ?? [],
            ],
        ]);
    }

    /**
     * JSON: open a new POS session for the current cashier.
     * Idempotent — if the user already has an open session, we just return it.
     */
    public function sessionStart(Request $request)
    {
        $userId = auth()->id();
        if (!$userId) {
            return response()->json(['ok' => false, 'message' => 'Not authenticated.'], 401);
        }

        $session = PosSession::openForUser($userId);
        if (!$session) {
            // Use property assignment (NOT mass-assign) so the Eloquent
            // 'datetime' cast runs and normalizes `started_at` to UTC — the
            // same way `ended_at` is normalized on End. Mass-assign would
            // write the local string verbatim, and a later diff against
            // ended_at would be off by the app's UTC offset (e.g. 8 hr).
            $session       = new PosSession();
            $session->user_id    = $userId;
            $session->started_at = Carbon::now();
            $session->save();
        }

        return response()->json([
            'ok'      => true,
            'session' => $this->serializeSession($session),
        ]);
    }

    /**
     * JSON: close the current cashier's open session.
     */
    public function sessionEnd(Request $request)
    {
        $userId = auth()->id();
        if (!$userId) {
            return response()->json(['ok' => false, 'message' => 'Not authenticated.'], 401);
        }

        $session = PosSession::openForUser($userId);
        if (!$session) {
            return response()->json([
                'ok'      => false,
                'message' => 'No active session to end.',
            ], 404);
        }

        $session->ended_at = Carbon::now();
        $session->save();

        return response()->json([
            'ok'      => true,
            'session' => $this->serializeSession($session),
        ]);
    }

    /**
     * JSON: list the current cashier's sessions, newest first, with each
     * session's running/closed total earnings computed live from sales.
     */
    public function sessionHistory(Request $request)
    {
        $userId = auth()->id();
        if (!$userId) {
            return response()->json(['ok' => false, 'message' => 'Not authenticated.'], 401);
        }

        $sessions = PosSession::query()
            ->where('user_id', $userId)
            ->orderByDesc('started_at')
            ->limit(50)
            ->get();

        return response()->json([
            'ok'       => true,
            'count'    => $sessions->count(),
            'sessions' => $sessions->map(function (PosSession $s) {
                return $this->serializeSession($s);
            }),
        ]);
    }

    /**
     * Shape a PosSession into the JSON the front-end expects.
     *
     * If the row was written before the timezone fix was deployed, the DB
     * still holds `started_at > ended_at` because the two values were
     * written into the wrong zones. For those rows we swap the *displayed*
     * start/end so the cashier sees the real chronological order — the
     * proper repair is `php artisan pos-sessions:fix-timezone`.
     */
    private function serializeSession(PosSession $s): array
    {
        $currency = settings('app_currency', '$');
        $start = $s->started_at;
        $end   = $s->ended_at;
        if ($start && $end && $start->greaterThan($end)) {
            [$start, $end] = [$end, $start];
        }
        return [
            'id'             => $s->id,
            'started_at'     => $start ? $start->format('Y-m-d H:i:s') : null,
            'ended_at'       => $end   ? $end->format('Y-m-d H:i:s')   : null,
            'status'         => $s->ended_at ? 'closed' : 'open',
            'total_earnings' => round($s->totalEarnings(), 2),
            'sale_count'     => $s->saleCount(),
            'duration_min'   => $s->durationMinutes(),
            'currency'       => $currency,
            'orders'         => $this->serializeSessionOrders($s),
        ];
    }

    private function serializeSessionOrders(PosSession $s): array
    {
        $rows = Sale::with('product.purchase.category')
            ->where('pos_session_id', $s->id)
            ->orderBy('created_at')
            ->get();

        $groups = [];
        foreach ($rows as $row) {
            $key = (
                ($row->customer_name ?? '') . '|' .
                ($row->payment_method ?? '') . '|' .
                ($row->payment_amount ?? '') . '|' .
                ($row->created_at ? $row->created_at->format('Y-m-d H:i') : '')
            );
            if (!isset($groups[$key])) {
                $groups[$key] = [
                    'first_id'       => $row->id,
                    'created_at'     => $row->created_at,
                    'customer_name'  => $row->customer_name,
                    'payment_method' => $row->payment_method,
                    'discount'       => (float) ($row->discount ?? 0),
                    'subtotal'       => 0.0,
                    'total'          => 0.0,
                    'items'          => [],
                ];
            }
            $groups[$key]['subtotal'] += (float) $row->total_price;
            $groups[$key]['total'] += (float) $row->total_price;
            $groups[$key]['items'][] = [
                'name'       => optional(optional($row->product)->purchase)->product ?? 'N/A',
                'category'   => optional(optional(optional($row->product)->purchase)->category)->name ?? 'Uncategorized',
                'qty'        => (int) $row->quantity,
                'unit_price' => $row->quantity ? round((float) $row->total_price / $row->quantity, 2) : 0.0,
                'line'       => (float) $row->total_price,
            ];
        }

        return collect($groups)
            ->sortByDesc(function ($group) {
                return $group['created_at'] ? $group['created_at']->timestamp : 0;
            })
            ->values()
            ->map(function ($group) {
                return [
                    'id'             => $group['first_id'],
                    'created_at'     => $group['created_at'] ? $group['created_at']->format('Y-m-d H:i') : null,
                    'customer_name'  => $group['customer_name'] ?? 'Walk-in customer',
                    'payment_method' => $group['payment_method'] ?? 'cash',
                    'subtotal'       => round($group['subtotal'], 2),
                    'discount'       => round($group['discount'], 2),
                    'total'          => round(max(0, $group['total'] - $group['discount']), 2),
                    'items'          => $group['items'],
                ];
            })
            ->all();
    }

    /**
     * Print-friendly receipt for one Sale row.
     *
     * Because PharMac's sales flow writes one Sale row per cart item
     * (no parent "Order" record), this method groups sibling rows that
     * share customer + payment method + payment amount AND were created
     * within the same minute — which is exactly the set of rows produced
     * by one Save click.
     */
    public function receipt(Sale $sale)
    {
        $siblings = collect();
        if ($sale->payment_method !== null && $sale->created_at !== null) {

            // Window: same minute as the target sale's created_at.
            $minuteStart = $sale->created_at->copy()->startOfMinute();
            $minuteEnd   = $sale->created_at->copy()->endOfMinute();

            $siblings = Sale::with('product.purchase')
                ->where('id', '!=', $sale->id)
                ->when($sale->customer_name !== null, function ($query) use ($sale) {
                    return $query->where('customer_name', $sale->customer_name);
                }, function ($query) use ($sale) {
                    return $query->whereNull('customer_name');
                })
                ->where('payment_method', $sale->payment_method)
                ->where('payment_amount', $sale->payment_amount)
                ->whereBetween('created_at', [$minuteStart, $minuteEnd])
                ->orderBy('id')
                ->get();
        }

        $items = collect([$sale])->merge($siblings);

        $subtotal = (float) $items->sum('total_price');
        $payment  = (float) ($sale->payment_amount ?? 0);
        $change   = (float) ($sale->change_amount ?? max(0, $payment - $subtotal));

        $companyName    = settings('app_name', config('app.name', 'PharMac'));
        $companyAddress = settings('company_address', 'Address : street, city, state 0000');
        $companyEmail   = settings('company_email',   'info@example.com');
        $companyPhone   = settings('company_phone',   '555-555-5555');
        $cashierName    = auth()->user()->name ?? 'Cashier';

        return view('admin.pos.receipt', compact(
            'sale', 'items', 'subtotal', 'payment', 'change',
            'companyName', 'companyAddress', 'companyEmail', 'companyPhone', 'cashierName'
        ));
    }
}
