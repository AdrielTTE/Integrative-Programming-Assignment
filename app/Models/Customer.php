<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Customer extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * Table name
     */
    protected $table = 'customer';

    /**
     * Primary key details
     */
    protected $primaryKey = 'customer_id';
    public $incrementing = false;
    protected $keyType = 'string';

    /**
     * Mass assignable attributes
     */
    protected $fillable = [
        'customer_id',
        'name',
        'email',
        'phone',
        'address',
        'status',
    ];

    /**
     * Attribute casting
     */
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Status constants
     */
    const STATUS_ACTIVE = 'active';
    const STATUS_INACTIVE = 'inactive';

    /**
     * Auto-generate customer_id when creating
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($customer) {
            if (empty($customer->customer_id)) {
                $customer->customer_id = self::generateCustomerId();
            }
        });
    }

    /**
     * Generate a unique customer ID
     */
    public static function generateCustomerId()
    {
        do {
            $customerId = 'CUST' . date('Ymd') . strtoupper(substr(md5(uniqid()), 0, 6));
        } while (self::where('customer_id', $customerId)->exists());

        return $customerId;
    }

    /**
     * Relationships
     */
    public function packages()
    {
        return $this->hasMany(Package::class, 'customer_id', 'customer_id');
    }
}
