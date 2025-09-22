<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;
use App\Factories\PackageStateFactory;
use App\States\Package\PackageState;

class Package extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'package';
    protected $primaryKey = 'package_id';
    public $incrementing = false;
    protected $keyType = 'string';

    /**
     * Mass-assignable attributes
     */
    protected $fillable = [
        'package_id',
        'user_id',
        'tracking_number',
        'package_weight',
        'package_dimensions',
        'package_contents',
        'sender_address',
        'recipient_address',
        'package_status',
        'priority',
        'shipping_cost',
        'estimated_delivery',
        'actual_delivery',
        'notes',
        'is_rated',
        'payment_status',
        'payment_id',
    ];

    /**
     * Attribute casting
     */
    protected $casts = [
        'package_weight'      => 'decimal:2',
        'shipping_cost'       => 'decimal:2',
        'created_at'          => 'datetime',
        'updated_at'          => 'datetime',
        'estimated_delivery'  => 'datetime',
        'actual_delivery'     => 'datetime',
        'is_rated'            => 'boolean',
        'payment_id'          => 'string',
    ];

    // Status constants
    const STATUS_PENDING        = 'pending';
    const STATUS_PROCESSING     = 'processing';
    const STATUS_IN_TRANSIT     = 'in_transit';
    const STATUS_OUT_FOR_DELIVERY = 'out_for_delivery';
    const STATUS_DELIVERED      = 'delivered';
    const STATUS_CANCELLED      = 'cancelled';
    const STATUS_RETURNED       = 'returned';
    const STATUS_FAILED         = 'failed';

    // Priority constants
    const PRIORITY_STANDARD = 'standard';
    const PRIORITY_EXPRESS  = 'express';
    const PRIORITY_URGENT   = 'urgent';

    // State management property
    private ?PackageState $currentState = null;

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($package) {
            if (empty($package->package_id)) {
                $package->package_id = self::generatePackageId();
            }
            if (empty($package->tracking_number)) {
                $package->tracking_number = self::generateTrackingNumber();
            }
            if (empty($package->package_status)) {
                $package->package_status = self::STATUS_PENDING;
            }
            if (empty($package->shipping_cost) && !empty($package->package_weight)) {
                $package->shipping_cost = $package->calculateShippingCost();
            }
            if (!isset($package->is_rated)) {
                $package->is_rated = false; // ✅ default to not rated
            }
        });

        static::updating(function ($package) {
            if ($package->isDirty('package_status')) {
                $oldStatus = $package->getOriginal('package_status');
                $newStatus = $package->package_status;
                \Log::info("Package {$package->package_id} status changed from {$oldStatus} to {$newStatus}");

                if ($newStatus === self::STATUS_DELIVERED && empty($package->actual_delivery)) {
                    $package->actual_delivery = now();
                }
            }
        });
    }

    /**
     * State methods
     */
    public function getState(): PackageState
    {
        if ($this->currentState === null) {
            $this->currentState = PackageStateFactory::create($this);
        }
        return $this->currentState;
    }

    public function setState(PackageState $state): void
    {
        $this->currentState = $state;
        $this->package_status = $state->getStatusName();
    }

    public function canBeEdited(): bool
    {
        return $this->getState()->canBeEdited();
    }

    public function canBeCancelled(): bool
    {
        return $this->getState()->canBeCancelled();
    }

    public function canBeAssigned(): bool
    {
        return $this->getState()->canBeAssigned();
    }

    public function getStatusColor(): string
    {
        return $this->getState()->getStatusColor();
    }

    public function getCurrentLocation(): string
    {
        return $this->getState()->getCurrentLocation();
    }

    /**
     * State transitions
     */
    public function process(array $data = []): PackageState
    {
        $newState = $this->getState()->process($data);
        $this->setState($newState);
        $this->save();
        return $newState;
    }

    public function cancel(User $user): PackageState
    {
        $newState = $this->getState()->cancel($user);
        $this->setState($newState);
        $this->save();
        return $newState;
    }

    public function assign($driverId): PackageState
    {
        $newState = $this->getState()->assign($driverId);
        $this->setState($newState);
        $this->save();
        return $newState;
    }

    public function deliver(array $proofData = []): PackageState
    {
        $newState = $this->getState()->deliver($proofData);
        $this->setState($newState);
        $this->save();
        return $newState;
    }

    /**
     * Relationships
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    public function delivery()
    {
        return $this->hasOne(Delivery::class, 'package_id', 'package_id');
    }

    public function payment()
    {
        return $this->hasOne(Payment::class, 'package_id', 'package_id');
    }

    public function assignment()
    {
        return $this->hasOne(DeliveryAssignment::class, 'package_id', 'package_id');
    }

    public function driver()
    {
        return $this->hasOneThrough(
            User::class,
            DeliveryAssignment::class,
            'package_id',
            'user_id',
            'package_id',
            'driver_id'
        );
    }

    /**
     * Scopes
     */
    public function scopeStatus($query, $status)
    {
        return $query->where('package_status', $status);
    }

    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeSearch($query, $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('tracking_number', 'like', "%{$search}%")
              ->orWhere('package_id', 'like', "%{$search}%")
              ->orWhere('sender_address', 'like', "%{$search}%")
              ->orWhere('recipient_address', 'like', "%{$search}%");
        });
    }

    /**
     * Static helpers
     */
    public static function generatePackageId()
    {
        $lastPackage = DB::table('package')
            ->where('package_id', 'like', 'P%')
            ->orderBy('package_id', 'desc')
            ->first();

        if ($lastPackage) {
            $number = (int) substr($lastPackage->package_id, 1);
            $nextNumber = $number + 1;
        } else {
            $nextNumber = 1;
        }

        return 'P' . str_pad($nextNumber, 3, '0', STR_PAD_LEFT);
    }

    public function getIsPaymentRequiredAttribute(): bool
    {
        return $this->payment_status !== 'paid' &&
            !in_array($this->package_status, ['cancelled', 'delivered']);
    }

    public static function generateTrackingNumber()
    {
        do {
            $trackingNumber = 'TRK' . date('ymd') . rand(100000, 999999);
        } while (self::where('tracking_number', $trackingNumber)->exists());

        return $trackingNumber;
    }

    public function calculateShippingCost()
    {
        $baseCost = 10.00;
        $weightCost = ($this->package_weight ?? 0) * 2.5;

        $dimensionCost = 0;
        if ($this->package_dimensions) {
            $dims = explode('x', strtolower($this->package_dimensions));
            if (count($dims) === 3) {
                $volume = array_product(array_map('floatval', $dims));
                if ($volume > 10000) {
                    $dimensionCost = ($volume / 10000) * 5;
                }
            }
        }

        $priorityMultiplier = 1;
        switch ($this->priority ?? self::PRIORITY_STANDARD) {
            case self::PRIORITY_EXPRESS:
                $priorityMultiplier = 1.5;
                break;
            case self::PRIORITY_URGENT:
                $priorityMultiplier = 2;
                break;
        }

        return round(($baseCost + $weightCost + $dimensionCost) * $priorityMultiplier, 2);
    }

    public static function getStatuses()
    {
        return [
            self::STATUS_PENDING        => 'Pending',
            self::STATUS_PROCESSING     => 'Processing',
            self::STATUS_IN_TRANSIT     => 'In Transit',
            self::STATUS_OUT_FOR_DELIVERY => 'Out for Delivery',
            self::STATUS_DELIVERED      => 'Delivered',
            self::STATUS_CANCELLED      => 'Cancelled',
            self::STATUS_RETURNED       => 'Returned',
            self::STATUS_FAILED         => 'Failed Delivery',
        ];
    }

    public static function getPriorities()
    {
        return [
            self::PRIORITY_STANDARD => 'Standard (5-7 days)',
            self::PRIORITY_EXPRESS  => 'Express (2-3 days)',
            self::PRIORITY_URGENT   => 'Urgent (1 day)',
        ];
    }

    public function getFormattedDetails()
    {
        return [
            'id'                  => $this->package_id,
            'tracking'            => $this->tracking_number,
            'status'              => $this->package_status,
            'status_text'         => self::getStatuses()[$this->package_status] ?? 'Unknown',
            'status_color'        => $this->getStatusColor(),
            'location'            => $this->getCurrentLocation(),
            'weight'              => $this->package_weight . ' kg',
            'dimensions'          => $this->package_dimensions,
            'cost'                => '$' . number_format($this->shipping_cost ?? 0, 2),
            'is_rated'            => $this->is_rated,
            'can_edit'            => $this->canBeEdited(),
            'can_cancel'          => $this->canBeCancelled(),
            'allowed_transitions' => $this->getState()->getAllowedTransitions()
        ];
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'user_id', 'customer_id');
    }

}

