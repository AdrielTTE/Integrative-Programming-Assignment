<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Delivery extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'delivery';
    protected $primaryKey = 'delivery_id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'delivery_id',
        'package_id',
        'driver_id',
        'delivery_status',
        'pickup_time',
        'delivery_time',
        'notes'
    ];

    protected $casts = [
        'pickup_time' => 'datetime',
        'delivery_time' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Relationships
    public function package()
    {
        return $this->belongsTo(Package::class, 'package_id', 'package_id');
    }

    public function driver()
    {
        return $this->belongsTo(User::class, 'driver_id');
    }
}

class DeliveryAssignment extends Model
{
    use HasFactory;

    protected $table = 'delivery_assignment';
    protected $primaryKey = 'assignment_id';

    protected $fillable = [
        'package_id',
        'driver_id',
        'assigned_at',
        'status'
    ];

    protected $casts = [
        'assigned_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Constants
    const STATUS_ASSIGNED = 'assigned';
    const STATUS_ACCEPTED = 'accepted';
    const STATUS_IN_PROGRESS = 'in_progress';
    const STATUS_COMPLETED = 'completed';
    const STATUS_CANCELLED = 'cancelled';

    // Relationships
    public function package()
    {
        return $this->belongsTo(Package::class, 'package_id', 'package_id');
    }

    public function driver()
    {
        return $this->belongsTo(User::class, 'driver_id');
    }
}

class User extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'status'
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    // Constants
    const ROLE_ADMIN = 'admin';
    const ROLE_DRIVER = 'driver';
    const ROLE_CUSTOMER = 'customer';

    // Relationships
    public function deliveryAssignments()
    {
        return $this->hasMany(DeliveryAssignment::class, 'driver_id');
    }

    public function deliveries()
    {
        return $this->hasMany(Delivery::class, 'driver_id');
    }
}