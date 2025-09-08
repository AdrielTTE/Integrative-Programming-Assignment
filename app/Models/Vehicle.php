<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Vehicle extends Model
{
    protected $table = 'vehicle'; // Laravel expects 'vehicles', so override

    protected $primaryKey = 'vehicle_id';
    public $incrementing = false;
    protected $keyType = 'string';

    public $timestamps = false;

    protected $fillable = [
        'vehicle_id',
        'vehicle_type',
        'vehicle_capacity',
        'vehicle_status',
        'last_maintenance_date',
    ];

    protected $casts = [
        'vehicle_capacity' => 'decimal:2',
        'last_maintenance_date' => 'date',
    ];

    // Relationships (example)
    public function deliveries()
    {
        return $this->hasMany(Delivery::class, 'vehicle_id', 'vehicle_id');
    }
}
