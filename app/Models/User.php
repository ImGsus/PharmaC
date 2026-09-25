<?php

namespace App\Models;

use App\Services\OrganizedFileStorage;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable,HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'name',
        'username',
        'gender',
        'email',
        'avatar',
        'password',
        'two_factor_enabled',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'two_factor_enabled' => 'boolean',
    ];

    protected static function booted()
    {
        static::deleting(function ($user) {
            if (!empty($user->avatar)) {
                app(OrganizedFileStorage::class)->delete('profiles', $user->avatar);
                $legacyPath = public_path('storage/users/'.basename($user->avatar));
                if (is_file($legacyPath)) @unlink($legacyPath);
            }
        });
    }
}
