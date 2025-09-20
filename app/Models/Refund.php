<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Refund extends Model
{
    protected $primaryKey = 'refund_id';
    public $timestamps = false;
    
    protected $fillable = [
        'payment_id', 'package_id', 'user_id',
        'refund_amount', 'reason', 'status',
        'request_date', 'process_date', 'admin_notes'
    ];

    public function payment()
    {
        return $this->belongsTo(Payment::class, 'payment_id');
    }
}