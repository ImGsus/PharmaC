<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Sale extends Model
{
    use HasFactory,SoftDeletes;

    protected $fillable = [
        'product_id','quantity','total_price',
        'customer_name','notes','payment_method','payment_amount','change_amount',
        'discount','cashier_id','pos_session_id',
    ];

    protected $casts = [
        'payment_amount' => 'decimal:2',
        'change_amount' => 'decimal:2',
        'total_price' => 'decimal:2',
        'discount' => 'decimal:2',
    ];

    public function product(){
        return $this->belongsTo(Product::class);
    }

    public function purchase(){
        return $this->belongsTo(Purchase::class);
    }

    public function cashier(){
        return $this->belongsTo(User::class, 'cashier_id');
    }

    public function session()
    {
        return $this->belongsTo(PosSession::class, 'pos_session_id');
    }

    /**
     * Scope sales to those recorded during a specific POS session.
     */
    public function scopeForSession($q, PosSession $session)
    {
        return $q->where('pos_session_id', $session->id)
                 ->where('cashier_id', $session->user_id);
    }
}
