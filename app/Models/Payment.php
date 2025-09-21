<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Payment extends Model
{
    use HasFactory;

    protected $table = 'payments';
    protected $primaryKey = 'payment_id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'payment_id',
        'package_id',
        'user_id',
        'amount',
        'payment_method',
        'transaction_id',
        'status',
        'payment_date',
        'payment_details',
        'currency'
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'payment_date' => 'datetime',
        'payment_details' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    // Relationships
    public function package()
    {
        return $this->belongsTo(Package::class, 'package_id', 'package_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    public function invoice()
    {
        return $this->hasOne(Invoice::class, 'payment_id', 'payment_id');
    }

    public function refund()
    {
        return $this->hasOne(Refund::class, 'payment_id', 'payment_id');
    }

    // Scopes
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    // Attributes
    public function getFormattedAmountAttribute()
    {
        return 'RM ' . number_format($this->amount, 2);
    }

    public function getIsRefundableAttribute()
    {
        if ($this->status !== 'completed') {
            return false;
        }
        
        if ($this->refund && in_array($this->refund->status, ['approved', 'pending'])) {
            return false;
        }
        
        $daysSincePayment = $this->payment_date->diffInDays(now());
        return $daysSincePayment <= 7;
    }
}