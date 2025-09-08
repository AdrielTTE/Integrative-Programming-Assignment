<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Admin extends Model
{
    protected $table = 'admin'; // Laravel would expect 'admins', so we override

    protected $primaryKey = 'admin_id';
    public $incrementing = false;
    protected $keyType = 'string';

    public $timestamps = false; // Since only 'last_login' is tracked

    protected $fillable = [
        'admin_id',
        'employee_id',
        'department',
        'access_level',
        'last_login',
    ];

    protected $casts = [
        'last_login' => 'datetime',
    ];

    // Relationships (optional examples)
    public function assignments()
    {
        return $this->hasMany(DeliveryAssignment::class, 'admin_id', 'admin_id');
    }
}
