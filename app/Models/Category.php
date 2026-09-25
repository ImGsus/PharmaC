<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'description', 'fixed_key', 'no_expiry'];

    protected $casts = [
        'description' => 'string',
        'fixed_key' => 'string',
        'no_expiry' => 'boolean',
    ];
}
