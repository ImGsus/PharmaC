<?php

namespace App\Http\Controllers\Admin;

use App\Models\Sale;
use App\Models\Category;
use App\Models\Purchase;
use App\Models\Product;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use App\Http\Controllers\Controller;

class DashboardController extends Controller
{
    public function index(){
        $title = 'dashboard';
        $total_purchases = Purchase::count();
        $total_categories = Category::count();
        $total_suppliers = Supplier::count();
        $total_sales = Sale::count();
        Product::markExpiredProducts();
        $total_barcoded_products = Product::whereNotNull('barcode')->count();
        $today = Carbon::today()->toDateString();
        
        $pieChart = app()->chartjs
                ->name('pieChart')
                ->type('pie')
                ->size(['width' => 320, 'height' => 220])
                ->labels(['Total Purchases', 'Total Suppliers','Total Sales'])
                ->datasets([
                    [
                        'backgroundColor' => ['#FF6384', '#36A2EB','#7bb13c'],
                        'hoverBackgroundColor' => ['#FF6384', '#36A2EB','#7bb13c'],
                        'data' => [$total_purchases, $total_suppliers,$total_sales]
                    ]
                ])
                ->options([
                    'responsive' => true,
                    'maintainAspectRatio' => false,
                    'legend' => ['position' => 'bottom'],
                    'layout' => ['padding' => ['top' => 8, 'bottom' => 8]],
                ]);
        
        $total_expired_products = Product::where('expired', true)->count();
        $latest_sales = Sale::whereDate('created_at', $today)->get();
        $today_sales = Sale::whereDate('created_at', $today)->sum('total_price');
        return view('admin.dashboard',compact(
            'title','pieChart','total_expired_products',
            'latest_sales','today_sales','total_categories','total_barcoded_products'
        ));
    }
}
