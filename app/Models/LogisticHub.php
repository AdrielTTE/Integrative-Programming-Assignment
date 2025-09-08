<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LogisticHub extends Model
{
    protected $table = 'logistichub'; // Table name as defined

    protected $primaryKey = 'hub_id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'hub_id',
        'hub_name',
        'hub_address',
        'hub_capacity',
        'hub_manager',
        'contact_number',
    ];

    // Relationships (optional)
    public function deliveryDetails()
    {
        return $this->hasMany(DeliveryDetails::class, 'hub_id', 'hub_id');
    }
}
