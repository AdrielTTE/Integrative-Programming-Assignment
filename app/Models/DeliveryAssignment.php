<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory; // <-- This import is essential
use Illuminate\Database\Eloquent\Model;

class DeliveryAssignment extends Model
{
    use HasFactory; // This trait allows for factory-based testing

    protected $table = 'deliveryassignment'; 

    protected $primaryKey = 'assignment_id';
    public $incrementing = false;
    protected $keyType = 'string';

    public $timestamps = false;

    protected $fillable = [
        'assignment_id',
        'admin_id',
        'package_id',
        'driver_id',
        'assigned_date',
        'assignment_status',
        'notes',
    ];

    protected $casts = [
        'assigned_date' => 'datetime',
    ];

    // Relationships
    public function admin()
    {
        return $this->belongsTo(Admin::class, 'admin_id', 'admin_id');
    }

    public function package()
    {
        return $this->belongsTo(Package::class, 'package_id', 'package_id');
    }

    public function driver()
    {
        return $this->belongsTo(DeliveryDriver::class, 'driver_id', 'driver_id');
    }
}