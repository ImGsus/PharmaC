<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\Sale;
use App\Models\StockMovement;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    private const REPORTS = [
        'current-stock' => ['title' => 'Current Stock Inventory', 'description' => 'Available stock by medicine, supplier, batch, expiry, and value.', 'icon' => 'fe-shopping-bag'],
        'low-stock' => ['title' => 'Low Stock Alert', 'description' => 'Products at or below their configured reorder level.', 'icon' => 'fe-check-circle'],
        'reorder-suggestions' => ['title' => 'Automated Reorder Suggestions', 'description' => 'Suggested replenishment quantities based on stock levels and recent dispensing.', 'icon' => 'fe-refresh-cw'],
        'expiry' => ['title' => 'Expiring and Expired Medicines', 'description' => 'Expiry status, days remaining, batch, and stock on hand.', 'icon' => 'fe-calendar'],
        'stock-movement' => ['title' => 'Stock Movement', 'description' => 'Opening, incoming, outgoing, and adjustment activity with running stock.', 'icon' => 'fe-activity'],
        'purchase-orders' => ['title' => 'Purchase Order Report', 'description' => 'Purchase references, suppliers, quantities, status, and delivery dates.', 'icon' => 'fe-shopping-bag'],
        'supplier-performance' => ['title' => 'Supplier Performance', 'description' => 'Supplier purchase volume, stock value, expiry, and delivery indicators.', 'icon' => 'fe-users'],
        'sales-dispensing' => ['title' => 'Sales and Dispensing Report', 'description' => 'Dispensed quantities, revenue, cashier, and customer details.', 'icon' => 'fe-bar-chart'],
        'demand-forecasting' => ['title' => 'Medicine Demand Forecasting', 'description' => 'Short-term demand estimates from recent dispensing history.', 'icon' => 'fe-trending-up'],
        'inventory-valuation' => ['title' => 'Inventory Valuation', 'description' => 'Current stock valued at recorded purchase cost and selling price.', 'icon' => 'fe-credit-card'],
        'batch-tracking' => ['title' => 'Batch and Lot Tracking', 'description' => 'Batch identity, expiry, original stock, remaining stock, and movement history.', 'icon' => 'fe-list-task'],
        'regulatory-compliance' => ['title' => 'Regulatory Compliance', 'description' => 'Exceptions for expiry, missing traceability data, barcode, and stock controls.', 'icon' => 'fe-shield'],
    ];

    public function index()
    {
        return view('admin.reports.index', ['title' => 'reports', 'reports' => self::REPORTS]);
    }

    public function show(Request $request, string $report)
    {
        abort_unless(isset(self::REPORTS[$report]), 404);
        Product::markExpiredProducts();

        $from = $request->date('from') ?: now()->subDays(30)->startOfDay();
        $to = $request->date('to') ?: now()->endOfDay();
        $rows = $this->rowsFor($report, $from, $to, $request);

        if ($request->boolean('embedded')) {
            return view('admin.reports._content', [
                'report' => $report,
                'definition' => self::REPORTS[$report],
                'rows' => $rows,
                'from' => $from->toDateString(),
                'to' => $to->toDateString(),
            ]);
        }

        return view('admin.reports.show', [
            'title' => self::REPORTS[$report]['title'],
            'report' => $report,
            'definition' => self::REPORTS[$report],
            'rows' => $rows,
            'from' => $from->toDateString(),
            'to' => $to->toDateString(),
            'currency' => settings('app_currency', '$'),
        ]);
    }

    public function export(Request $request, string $report): StreamedResponse
    {
        abort_unless(isset(self::REPORTS[$report]), 404);
        Product::markExpiredProducts();
        $from = $request->date('from') ?: now()->subDays(30)->startOfDay();
        $to = $request->date('to') ?: now()->endOfDay();
        $rows = $this->rowsFor($report, $from, $to, $request);
        $filename = $report . '-' . now()->format('Ymd-His') . '.csv';

        return response()->streamDownload(function () use ($rows) {
            $out = fopen('php://output', 'w');
            if ($rows->isNotEmpty()) {
                fputcsv($out, array_keys($rows->first()));
                foreach ($rows as $row) fputcsv($out, array_values($row));
            }
            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv']);
    }

    private function rowsFor(string $report, Carbon $from, Carbon $to, Request $request)
    {
        $currency = settings('app_currency', '$');
        $formatMoney = static fn ($value) => $currency . ' ' . number_format((float) $value, 2);

        if ($report === 'current-stock' || $report === 'inventory-valuation') {
            return Purchase::with(['category', 'supplier', 'purchaseProduct'])
                ->where('quantity', '>', 0)->orderBy('product')->get()->map(function ($purchase) use ($formatMoney) {
                    $costValue = (float) $purchase->quantity * (float) $purchase->cost_price;
                    $salePrice = (float) optional($purchase->purchaseProduct)->price;
                    return [
                        'Product' => $purchase->product,
                        'Category' => optional($purchase->category)->name ?: 'Uncategorised',
                        'Supplier' => optional($purchase->supplier)->name ?: 'Unknown',
                        'Batch' => $purchase->batch_number ?: 'Unassigned',
                        'Quantity' => $purchase->quantity,
                        'Expiry' => $purchase->expiry_date ?: 'No expiry',
                        'Cost Value' => $formatMoney($costValue),
                        'Selling Value' => $formatMoney((float) $purchase->quantity * $salePrice),
                    ];
                });
        }

        if ($report === 'low-stock') {
            return Purchase::with(['category', 'supplier'])->whereColumn('quantity', '<=', 'reorder_level')->orderBy('quantity')->get()->map(fn ($p) => [
                'Product' => $p->product,
                'Category' => optional($p->category)->name ?: 'Uncategorised',
                'Supplier' => optional($p->supplier)->name ?: 'Unknown',
                'Batch' => $p->batch_number ?: 'Unassigned',
                'On Hand' => $p->quantity,
                'Reorder Level' => $p->reorder_level,
                'Shortage' => max(0, (int) $p->reorder_level - (int) $p->quantity),
            ]);
        }

        if ($report === 'reorder-suggestions') {
            $days = max(1, $from->diffInDays($to) + 1);
            $sales = Sale::whereBetween('created_at', [$from, $to])
                ->select('product_id', DB::raw('SUM(quantity) as units'))
                ->groupBy('product_id')->get()->keyBy('product_id');

            return Purchase::with(['supplier', 'purchaseProduct'])->whereColumn('quantity', '<=', 'reorder_level')->orderBy('quantity')->get()->map(function ($p) use ($sales, $days) {
                $sold = (int) optional($sales->get(optional($p->purchaseProduct)->id))->units;
                $daily = $sold / $days;
                $target = max((int) $p->reorder_level * 2, (int) ceil($daily * 14));
                return [
                    'Product' => $p->product,
                    'Supplier' => optional($p->supplier)->name ?: 'Unknown',
                    'On Hand' => $p->quantity,
                    'Reorder Level' => $p->reorder_level,
                    'Recent Daily Demand' => number_format($daily, 2),
                    'Suggested Order' => max(0, $target - (int) $p->quantity),
                    'Basis' => $sold > 0 ? 'Stock + recent sales' : 'Stock threshold',
                ];
            });
        }

        if ($report === 'expiry') {
            return Purchase::with(['category', 'supplier'])->whereNotNull('expiry_date')->whereDate('expiry_date', '<=', now()->addDays(90))->orderBy('expiry_date')->get()->map(function ($p) {
                $expiry = Carbon::parse($p->expiry_date);
                return [
                    'Product' => $p->product,
                    'Batch' => $p->batch_number ?: 'Unassigned',
                    'Supplier' => optional($p->supplier)->name ?: 'Unknown',
                    'Quantity' => $p->quantity,
                    'Expiry Date' => $expiry->toDateString(),
                    'Status' => $expiry->isPast() ? 'Expired' : ($expiry->diffInDays(now()) <= 30 ? 'Critical' : 'Expiring soon'),
                    'Days' => $expiry->isPast() ? 0 : $expiry->diffInDays(now()),
                ];
            });
        }

        if ($report === 'stock-movement') {
            return StockMovement::with(['purchase', 'user'])->whereBetween('created_at', [$from, $to])->latest()->get()->map(fn ($m) => [
                'Date' => optional($m->created_at)->format('Y-m-d H:i'),
                'Type' => strtoupper($m->movement_type),
                'Product' => optional($m->purchase)->product ?: 'Removed product',
                'Batch' => $m->batch_number ?: 'Unassigned',
                'Quantity' => $m->quantity,
                'Balance' => $m->quantity_after,
                'Reference' => trim(($m->reference_type ?: '') . ' #' . ($m->reference_id ?: '')),
                'User' => optional($m->user)->name ?: 'System',
            ]);
        }

        if ($report === 'purchase-orders') {
            return Purchase::with(['supplier', 'category'])->whereBetween('created_at', [$from, $to])->latest()->get()->map(fn ($p) => [
                'Order Number' => $p->order_number ?: 'PO-' . str_pad($p->id, 6, '0', STR_PAD_LEFT),
                'Order Date' => optional($p->created_at)->toDateString(),
                'Supplier' => optional($p->supplier)->name ?: 'Unknown',
                'Product' => $p->product,
                'Quantity' => $p->quantity,
                'Status' => ucfirst($p->status ?: 'received'),
                'Expected Delivery' => $p->expected_delivery_date ?: 'Not set',
                'Received Date' => $p->received_date ?: optional($p->created_at)->toDateString(),
                'Value' => $formatMoney((float) $p->quantity * (float) $p->cost_price),
            ]);
        }

        if ($report === 'supplier-performance') {
            return Purchase::with('supplier')->whereBetween('created_at', [$from, $to])->get()->groupBy('supplier_id')->map(function ($items) use ($formatMoney) {
                $supplier = optional($items->first()->supplier)->name ?: 'Unknown';
                return [
                    'Supplier' => $supplier,
                    'Purchase Orders' => $items->count(),
                    'Units Received' => $items->sum('quantity'),
                    'Stock Value' => $formatMoney($items->sum(fn ($p) => (float) $p->quantity * (float) $p->cost_price)),
                    'Expired Lines' => $items->filter(fn ($p) => $p->expiry_date && Carbon::parse($p->expiry_date)->isPast())->count(),
                    'On Time Data' => $items->every(fn ($p) => !$p->expected_delivery_date || !$p->received_date || $p->received_date <= $p->expected_delivery_date) ? 'Yes' : 'Review',
                ];
            })->values();
        }

        if ($report === 'sales-dispensing') {
            return Sale::with(['product.purchase', 'cashier'])->whereBetween('created_at', [$from, $to])->latest()->get()->map(fn ($s) => [
                'Date' => optional($s->created_at)->format('Y-m-d H:i'),
                'Product' => optional(optional($s->product)->purchase)->product ?: 'Removed product',
                'Batch' => optional(optional($s->product)->purchase)->batch_number ?: 'Unassigned',
                'Quantity' => $s->quantity,
                'Total' => $formatMoney($s->total_price),
                'Customer' => $s->customer_name ?: 'Walk-in',
                'Cashier' => optional($s->cashier)->name ?: 'Unknown',
                'Payment' => ucfirst($s->payment_method ?: 'cash'),
            ]);
        }

        if ($report === 'demand-forecasting') {
            $days = max(1, $from->diffInDays($to) + 1);
            return Sale::with('product.purchase')->whereBetween('created_at', [$from, $to])->get()
                ->groupBy('product_id')->map(function ($items) use ($days) {
                    $product = optional($items->first()->product);
                    $purchase = optional($product->purchase);
                    $units = (int) $items->sum('quantity');
                    $daily = $units / $days;
                    return [
                        'Product' => $purchase->product ?: 'Removed product',
                        'Units Sold' => $units,
                        'Average Daily Demand' => number_format($daily, 2),
                        '7 Day Forecast' => (int) ceil($daily * 7),
                        '30 Day Forecast' => (int) ceil($daily * 30),
                        'Current Stock' => $purchase->quantity ?: 0,
                    ];
                })->values()->sortByDesc('30 Day Forecast')->values();
        }

        if ($report === 'batch-tracking') {
            return Purchase::with(['supplier', 'purchaseProduct'])->whereNotNull('batch_number')->orderBy('batch_number')->get()->map(fn ($p) => [
                'Batch Number' => $p->batch_number,
                'Product' => $p->product,
                'Supplier' => optional($p->supplier)->name ?: 'Unknown',
                'Manufacture Date' => $p->manufacture_date ?: 'Not set',
                'Expiry Date' => $p->expiry_date ?: 'No expiry',
                'Original Stock' => $p->total_quantity ?: $p->quantity,
                'Remaining Stock' => $p->quantity,
                'Barcode' => optional($p->purchaseProduct)->barcode ?: 'Missing',
            ]);
        }

        return Purchase::with(['category', 'supplier', 'purchaseProduct'])->get()->flatMap(function ($p) {
            $issues = [];
            if ($p->quantity <= $p->reorder_level) $issues[] = 'Low stock';
            if (!$p->batch_number) $issues[] = 'Missing batch number';
            if (!$p->purchaseProduct || !$p->purchaseProduct->barcode) $issues[] = 'Missing barcode';
            if ($p->expiry_date && Carbon::parse($p->expiry_date)->isPast()) $issues[] = 'Expired stock';
            if (!$p->expiry_date && !optional($p->category)->no_expiry) $issues[] = 'Missing expiry date';
            return $issues ? [[
                'Product' => $p->product,
                'Supplier' => optional($p->supplier)->name ?: 'Unknown',
                'Batch' => $p->batch_number ?: 'Missing',
                'Quantity' => $p->quantity,
                'Issues' => implode('; ', $issues),
                'Recommended Action' => 'Review and document corrective action',
            ]] : [];
        })->values();
    }
}
