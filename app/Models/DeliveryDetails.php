<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DeliveryDetails extends Model
{
    protected $table = 'deliverydetails'; // Table name

    protected $primaryKey = 'detail_id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'detail_id',
        'delivery_id',
        'hub_id',
        'arrival_time',
        'departure_time',
        'processing_status',
    ];

    protected $casts = [
        'arrival_time' => 'datetime',
        'departure_time' => 'datetime',
    ];

    // Relationships

    public function delivery()
    {
        return $this->belongsTo(Delivery::class, 'delivery_id', 'delivery_id');
    }

    public function hub()
    {
        return $this->belongsTo(LogisticHub::class, 'hub_id', 'hub_id');
    }
}
