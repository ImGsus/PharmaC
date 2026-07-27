<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Purchase extends Model
{
    use HasFactory;

    protected $fillable = [
        'product','category_id','supplier_id',
        'cost_price','quantity','expiry_date',
        'image','item_quantity','packaging_box','quantity_per_box','total_quantity'
    ];

    public function supplier(){
        return $this->belongsTo(Supplier::class);
    }

    public function category(){
        return $this->belongsTo(Category::class);
    }

    public function purchaseProduct(){
        return $this->hasOne(Product::class);
    }

    public function getImageUrlAttribute()
    {
        if (empty($this->image)) {
            return asset('assets/img/avatar.png');
        }

        $imagePath = public_path('storage/purchases/'.$this->image);
        if (file_exists($imagePath)) {
            return asset('storage/purchases/'.$this->image);
        }

        return asset('assets/img/avatar.png');
    }
}
