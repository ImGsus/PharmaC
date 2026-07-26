<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

class Product extends Model
{
    use HasFactory,SoftDeletes;
    protected $fillable = [
        'purchase_id','price',
        'discount','description','barcode','expired',
    ];

    public function purchase(){
        return $this->belongsTo(Purchase::class);
    }

    public static function markExpiredProducts()
    {
        return static::where('expired', false)
            ->whereHas('purchase', function ($query) {
                $query->whereDate('expiry_date', '<=', Carbon::today());
            })
            ->update(['expired' => true]);
    }
}
