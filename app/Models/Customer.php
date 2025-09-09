<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    protected $table = 'customer';
    protected $primaryKey = 'customer_id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'customer_id',
        'first_name',
        'last_name',
        'address',
        'date_of_birth',
        'customer_status',
    ];

    protected $casts = [
        'date_of_birth' => 'date',
    ];

    // Example accessor (optional)
    public function getFullNameAttribute()
    {
        return "{$this->first_name} {$this->last_name}";
    }

    // Example relationships (assuming you have packages or deliveries linked to customers)
    public function packages()
    {
        return $this->hasMany(Package::class, 'customer_id', 'customer_id');
    }
}
