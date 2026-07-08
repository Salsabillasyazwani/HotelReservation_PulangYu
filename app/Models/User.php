<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'role_id',
        'name',
        'username',
        'email',
        'phone_number',
        'status',
        'avatar',
        'password',
        'last_login_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'last_login_at'     => 'datetime',
            'password'          => 'hashed',
        ];
    }

    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    public function guest()
    {
        return $this->hasOne(Guest::class);
    }

    /**
     * Alias supaya di Blade/JS lama yang pakai istilah "photo"
     * tetap konsisten tanpa perlu rename kolom avatar di DB.
     */
    public function getPhotoAttribute()
    {
        return $this->avatar;
    }
}
