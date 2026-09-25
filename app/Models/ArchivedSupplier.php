<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ArchivedSupplier extends Model
{
    protected $fillable = [
        'supplier_name',
        'archived_at',
        'data',
    ];

    protected $casts = [
        'archived_at' => 'datetime',
        'data' => 'array',
    ];
}
