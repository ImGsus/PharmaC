<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ArchiveEntry extends Model
{
    protected $fillable = [
        'model_type',
        'model_id',
        'label',
        'deleted_at',
        'data',
    ];

    protected $casts = [
        'deleted_at' => 'datetime',
        'data' => 'array',
    ];
}
