<?php

namespace App\Models;

use App\Services\OrganizedFileStorage;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Purchase extends Model
{
    use HasFactory;

    protected $fillable = [
        'product','category_id','supplier_id',
        'cost_price','quantity','expiry_date',
        'image','item_quantity','packaging_box','quantity_per_box','total_quantity',
        'batch_number','manufacture_date','reorder_level','order_number',
        'expected_delivery_date','received_date','status'
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

    public function stockMovements()
    {
        return $this->hasMany(StockMovement::class);
    }

    /**
     * On-disk absolute path of the uploaded image, or null.
     * Uses storage/app/purchases/ (NOT public/storage/purchases/) so the
     * upload stays out of OneDrive's reach on this project.
     */
    public function getImagePathAttribute()
    {
        if (empty($this->image)) return null;
        $storage = app(OrganizedFileStorage::class);
        $organizedPath = $storage->path('purchases', $this->image);
        return is_file($organizedPath) ? $organizedPath : $storage->legacyPath('purchases', $this->image);
    }

    /**
    * Public URL of the uploaded image, or the product placeholder.
     * Always returns something safe to drop into an <img src>.
     */
    public function getImageUrlAttribute()
    {
        if (empty($this->image)) {
            return asset('assets/img/productnoimage.png');
        }

        $path = $this->getImagePathAttribute();
        if ($path && file_exists($path)) {
            // Route defined in routes/web.php — streams the file via Response::file().
            return url('storage/system/purchases/'.$this->image);
        }

        return asset('assets/img/productnoimage.png');
    }
}
