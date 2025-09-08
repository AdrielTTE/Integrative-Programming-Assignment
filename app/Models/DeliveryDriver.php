<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DeliveryDriver extends Model
{
    protected $table = 'deliverydriver'; // Table is not plural

    protected $primaryKey = 'driver_id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'driver_id',
        'first_name',
        'last_name',
        'license_number',
        'hire_date',
        'driver_status',
    ];

    protected $casts = [
        'hire_date' => 'date',
    ];

    // Accessor for full name (optional)
    public function getFullNameAttribute()
    {
        return "{$this->first_name} {$this->last_name}";
    }

    // Relationships (example)
    public function deliveries()
    {
        return $this->hasMany(Delivery::class, 'driver_id', 'driver_id');
    }

    public function assignments()
    {
        return $this->hasMany(DeliveryAssignment::class, 'driver_id', 'driver_id');
    }
}
