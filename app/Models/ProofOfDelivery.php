<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProofOfDelivery extends Model
{
    protected $table = 'proofofdelivery';
    protected $primaryKey = 'proof_id';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    /**
     * VERIFY: This array must include all fields we intend to update.
     */
    protected $fillable = [
        'proof_id',
        'delivery_id',
        'proof_type',
        'proof_url',
        'recipient_signature_name',
        'timestamp_created',
        'verification_status',
        'verified_at',
        'verified_by',
        'notes',
    ];

    protected $casts = [
        'timestamp_created' => 'datetime',
        'verified_at' => 'datetime',
    ];

    public function delivery()
    {
        return $this->belongsTo(Delivery::class, 'delivery_id', 'delivery_id');
    }

    public function verifier()
    {
        return $this->belongsTo(User::class, 'verified_by', 'user_id');
    }
}