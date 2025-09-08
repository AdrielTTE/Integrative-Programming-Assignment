<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProofOfDelivery extends Model
{
    protected $table = 'proofofdelivery'; // Singular custom table name

    protected $primaryKey = 'proof_id';
    public $incrementing = false;
    protected $keyType = 'string';

    public $timestamps = false; // You're using custom timestamp column `timestamp_created`

    protected $fillable = [
        'proof_id',
        'delivery_id',
        'proof_type',
        'proof_url',
        'recipient_signature_name',
        'timestamp_created',
    ];

    protected $casts = [
        'timestamp_created' => 'datetime',
    ];

    // Relationships

    public function delivery()
    {
        return $this->belongsTo(Delivery::class, 'delivery_id', 'delivery_id');
    }
}
