<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $primaryKey = 'payment_id';
    public $timestamps = false;
    
    protected $fillable = [
        'transaction_id', 'package_id', 'user_id', 
        'amount', 'payment_method', 'status', 
        'payment_date', 'invoice_number'
    ];

    public function refunds()
    {
        return $this->hasMany(Refund::class, 'payment_id');
    }
}