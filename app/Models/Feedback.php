<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\Customer;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Feedback extends Model
{

     protected $table = 'feedback';

    // Primary key
    protected $primaryKey = 'feedback_id';

    // If primary key is not auto-incrementing
    public $incrementing = false;

    // Primary key type
    protected $keyType = 'string';

    // Timestamps
    public $timestamps = true;

    // Mass assignable attributes
    protected $fillable = [
        'feedback_id',
        'delivery_id',
        'customer_id',
        'rating',
        'comment',
        'created_at',
        'updated_at'
    ];

    // Relationships
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    public function delivery(): BelongsTo
    {
        return $this->belongsTo(Delivery::class, 'delivery_id');
    }


}
