<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Route extends Model
{
    protected $table = 'route'; // Laravel would expect 'routes', so override it

    protected $primaryKey = 'route_id';
    public $incrementing = false;
    protected $keyType = 'string';

    public $timestamps = false;

    protected $fillable = [
        'route_id',
        'route_name',
        'start_location',
        'end_location',
        'estimated_duration_minutes',
        'distance_km',
    ];

    protected $casts = [
        'estimated_duration_minutes' => 'integer',
        'distance_km' => 'decimal:2',
    ];

    // Relationships (example)
    public function deliveries()
    {
        return $this->hasMany(Delivery::class, 'route_id', 'route_id');
    }
}
