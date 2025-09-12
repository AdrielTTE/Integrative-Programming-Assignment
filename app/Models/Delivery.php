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
        'actual_delivery_time',
        'notes'
    ];

    protected $casts = [
        'pickup_time' => 'datetime',
        'delivery_time' => 'datetime',
        'actual_delivery_time' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
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