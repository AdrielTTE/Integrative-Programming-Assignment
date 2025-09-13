<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;
    protected $rememberTokenName = null;
    protected $table = 'user';
    protected $primaryKey = 'user_id';
    public $timestamps = false;
 public $incrementing = false;
    protected $keyType = 'string';

    const ROLE_ADMIN = 'admin';
    const ROLE_DRIVER = 'driver';
    const ROLE_CUSTOMER = 'customer';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'user_id',
        'username',
        'email',
        'password',
        'phone_number',
    ];

    /**
     * The attributes that should be hidden for serialization.
     */
    protected $hidden = [
        'password',

    ];

    protected $casts = [
        'created_at'=> 'datetime',
        'email_verified_at' => 'datetime',
        ];

    /**
     * The attributes that should be cast.
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed', // Use the 'hashed' cast for automatic hashing
            'created_at' => 'datetime',
        ];
    }
}
