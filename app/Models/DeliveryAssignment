<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DeliveryAssignment extends Model
{
    protected $table = 'deliveryassignment'; // Table name is not plural

    protected $primaryKey = 'assignment_id';
    public $incrementing = false;
    protected $keyType = 'string';

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
