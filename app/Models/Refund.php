<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Refund extends Model
{
    use HasFactory;

    protected $table = 'refunds';
    protected $primaryKey = 'refund_id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'refund_id',
        'payment_id',
        'user_id',
        'amount',
        'reason',
        'status',
        'requested_at',
        'processed_at',
        'processed_by',
        'admin_notes',
        'refund_transaction_id'
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'requested_at' => 'datetime',
        'processed_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    // Relationships
    public function payment()
    {
        return $this->belongsTo(Payment::class, 'payment_id', 'payment_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    public function processedBy()
    {
        return $this->belongsTo(User::class, 'processed_by', 'user_id');
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function scopeRejected($query)
    {
        return $query->where('status', 'rejected');
    }

    // Attributes
    public function getFormattedAmountAttribute()
    {
        return 'RM ' . number_format($this->amount, 2);
    }

    public function getStatusColorAttribute()
    {
        return match($this->status) {
            'pending' => 'warning',
            'approved' => 'success',
            'rejected' => 'danger',
            'processed' => 'info',
            default => 'secondary'
        };
    }

    public function getDaysWaitingAttribute()
    {
        if ($this->status !== 'pending') {
            return null;
        }
        
        return $this->requested_at->diffInDays(now());
    }
}