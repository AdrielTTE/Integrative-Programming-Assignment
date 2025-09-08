<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Package extends Model
{
    protected $table = 'package'; // Singular table name

    protected $primaryKey = 'package_id';
    public $incrementing = false;
    protected $keyType = 'string';

    public $timestamps = false; // Because only `created_at` is present (no `updated_at`)

    protected $fillable = [
        'package_id',
        'customer_id',
        'tracking_number',
        'package_weight',
        'package_dimensions',
        'package_contents',
        'sender_address',
        'recipient_address',
        'package_status',
        'created_at',
    ];

    protected $casts = [
        'package_weight' => 'decimal:2',
        'created_at' => 'datetime',
    ];

    // Relationships

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id', 'customer_id');
    }

    public function delivery()
    {
        return $this->hasOne(Delivery::class, 'package_id', 'package_id');
    }

    public function assignment()
    {
        return $this->hasOne(DeliveryAssignment::class, 'package_id', 'package_id');
    }
}
