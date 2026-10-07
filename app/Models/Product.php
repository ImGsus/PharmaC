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
        'discount','description','barcode','expired','is_active',
    ];

    public function purchase(){
        return $this->belongsTo(Purchase::class);
    }

    public static function markExpiredProducts()
    {
        $today = Carbon::today()->format('Y-m-d');
        $count = static::where('expired', false)
            ->whereHas('purchase', function ($query) use ($today) {
                $query->whereDate('expiry_date', '<=', $today);
            })
            ->update(['expired' => true]);

        $unmarked = static::where('expired', false)
            ->whereHas('purchase', function ($query) {
                $query->whereNotNull('box_expiries');
            })
            ->with('purchase')
            ->get();

        foreach ($unmarked as $prod) {
            $boxes = $prod->purchase->box_expiries;
            if (is_array($boxes)) {
                foreach ($boxes as $b) {
                    if (!empty($b['expiry_date']) && $b['expiry_date'] <= $today) {
                        $prod->update(['expired' => true]);
                        $count++;
                        break;
                    }
                }
            }
        }

        return $count;
    }
}
