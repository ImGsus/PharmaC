<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Purchase;
use Illuminate\Http\Request;

class InventoryCheckController extends Controller
{
    public function index(Request $request)
    {
        $search = trim((string) $request->input('q', ''));
        $stock = Purchase::with(['purchaseProduct', 'supplier'])
            ->where('quantity', '>', 0)
            ->when($search, function ($query) use ($search) {
                $query->where(function ($nested) use ($search) {
                    $nested->where('product', 'like', '%' . $search . '%')
                        ->orWhere('batch_number', 'like', '%' . $search . '%')
                        ->orWhereHas('purchaseProduct', function ($product) use ($search) {
                            $product->where('barcode', $search);
                        });
                });
            })
            ->orderBy('product')->paginate(25)->withQueryString();

        return view('admin.inventory-check.index', [
            'title' => 'mobile inventory check',
            'stock' => $stock,
            'search' => $search,
        ]);
    }
}