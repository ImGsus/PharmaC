<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GeminiApiCredential extends Model
{
    protected $fillable = ['user_id', 'name', 'encrypted_api_key'];

    protected $hidden = ['encrypted_api_key'];
}
