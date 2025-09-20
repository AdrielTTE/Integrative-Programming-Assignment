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

    // --- FIX #1: Tell Laravel your table does NOT have created_at/updated_at columns ---
    public $timestamps = false;

    /**
     * --- FIX #2: The fillable properties MUST match your database table columns ---
     */
    protected $fillable = [
        'delivery_id',
        'package_id',
        'driver_id',
        'vehicle_id', // This was missing
        'route_id',
        'pickup_time',
        'estimated_delivery_time', // This name matches your controller
        'actual_delivery_time',
        'delivery_status',
        'delivery_cost',
    ];

    /**
     * The attributes that should be cast to native types.
     */
    protected $casts = [
        'pickup_time' => 'datetime',
        'estimated_delivery_time' => 'datetime',
        'actual_delivery_time' => 'datetime',
        'delivery_cost' => 'decimal:2'
    ];

    public function package()
    {
        return $this->belongsTo(Package::class, 'package_id', 'package_id');
    }

    public function driver()
    {
        return $this->belongsTo(DeliveryDriver::class, 'driver_id', 'driver_id');
    }
}