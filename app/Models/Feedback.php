<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Customer;
use App\Models\Delivery;

class Feedback extends Model
{
    use HasFactory;

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
        'category',
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
