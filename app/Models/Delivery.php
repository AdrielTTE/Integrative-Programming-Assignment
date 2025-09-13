<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Observers\Subject;
use App\Observers\Observer;
use Illuminate\Http\Request;

class Delivery extends Model implements Subject
{
    use HasFactory, SoftDeletes;

    protected $table = 'delivery';
    protected $primaryKey = 'delivery_id';
    public $incrementing = false;
    protected $keyType = 'string';
    private array $observers = [];

    protected $fillable = [
        'delivery_id',
        'package_id',
        'driver_id',
        'delivery_status',
        'pickup_time',
        'delivery_time',
        'actual_delivery_time',
        'notes'
    ];

    protected $casts = [
        'pickup_time' => 'datetime',
        'delivery_time' => 'datetime',
        'actual_delivery_time' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function package()
    {
        return $this->belongsTo(Package::class, 'package_id', 'package_id');
    }

    public function driver()
    {
        return $this->belongsTo(DeliveryDriver::class, 'driver_id', 'driver_id');
    }


    function addObserver(Observer $observer){
        $this->observers()->attach($observer);
    }
    function removeObserver(Observer $observer){
        $this->observers()->detach($observer);
    }
    function notifyObserver(){
foreach ($this->observers as $observer) {
            $observer->update($this);
        }
    }

    public function updateStatus(string $status)
    {
        $this->delivery_status = $status;
        $this->save();

        $this->notifyObserver(); // 🔔 notify
    }


}
