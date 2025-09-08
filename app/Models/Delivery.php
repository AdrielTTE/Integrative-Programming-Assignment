<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Delivery extends Model
{
    protected $table = 'delivery'; // Table name is not plural

    protected $primaryKey = 'delivery_id'; // Custom primary key
    public $incrementing = false;          // Because it's varchar, not auto-incrementing
    protected $keyType = 'string';         // Primary key is varchar

    protected $fillable = [
        'delivery_id',
        'package_id',
        'driver_id',
        'vehicle_id',
        'route_id',
        'pickup_time',
        'estimated_delivery_time',
        'actual_delivery_time',
        'delivery_status',
        'delivery_cost',
    ];

    protected $casts = [
        'pickup_time' => 'datetime',
        'estimated_delivery_time' => 'datetime',
        'actual_delivery_time' => 'datetime',
        'delivery_cost' => 'decimal:2',
    ];

    // Example relationships
    public function driver()
    {
        return $this->belongsTo(DeliveryDriver::class, 'driver_id', 'driver_id');
    }

    public function package()
    {
        return $this->belongsTo(Package::class, 'package_id', 'package_id');
    }

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class, 'vehicle_id', 'vehicle_id');
    }

    public function route()
    {
        return $this->belongsTo(Route::class, 'route_id', 'route_id');
    }
}
