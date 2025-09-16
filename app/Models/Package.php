<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
// Import the User model
use App\Models\User; 
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;


class Package extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The table associated with the model.
     */
    protected $table = 'package';

    /**
     * The primary key associated with the table.
     */
    protected $primaryKey = 'package_id';

    /**
     * Indicates if the IDs are auto-incrementing.
     */
    public $incrementing = false;

    /**
     * The "type" of the auto-incrementing ID.
     */
    protected $keyType = 'string';

    /**
     * The attributes that are mass assignable.
     * --- MODIFIED ---
     */
    protected $fillable = [
        'package_id',
        'user_id', // Changed from 'customer_id'
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
        'notes'
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'package_weight' => 'decimal:2',
        'shipping_cost' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'estimated_delivery' => 'datetime',
        'actual_delivery' => 'datetime',
    ];

    /**
     * Package status constants
     */
    const STATUS_PENDING = 'pending';
    const STATUS_PROCESSING = 'processing';
    const STATUS_IN_TRANSIT = 'in_transit';
    const STATUS_OUT_FOR_DELIVERY = 'out_for_delivery';
    const STATUS_DELIVERED = 'delivered';
    const STATUS_CANCELLED = 'cancelled';
    const STATUS_RETURNED = 'returned';
    const STATUS_FAILED = 'failed';

    /**
     * Priority constants
     */
    const PRIORITY_STANDARD = 'standard';
    const PRIORITY_EXPRESS = 'express';
    const PRIORITY_URGENT = 'urgent';

    /**
     * Boot method to handle model events
     */
    protected static function boot()
    {
        parent::boot();

        // Auto-generate package_id and tracking_number if not provided
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
            // Calculate shipping cost if not set
            if (empty($package->shipping_cost) && !empty($package->package_weight)) {
                $package->shipping_cost = $package->calculateShippingCost();
            }
        });

        // Log status changes
        static::updating(function ($package) {
            if ($package->isDirty('package_status')) {
                $oldStatus = $package->getOriginal('package_status');
                $newStatus = $package->package_status;

                // Create audit log (implement PackageStatusHistory model if needed)
                \Log::info("Package {$package->package_id} status changed from {$oldStatus} to {$newStatus}");

                // Update delivery times based on status
                if ($newStatus === self::STATUS_DELIVERED && empty($package->actual_delivery)) {
                    $package->actual_delivery = now();
                }
            }
        });
    }

    /**
     * Relationships
     */

    /**
     * Get the user that owns the package.
     * --- MODIFIED ---
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }


    /**
     * Get the delivery associated with the package.
     */
    public function delivery()
    {
        return $this->hasOne(Delivery::class, 'package_id', 'package_id');
    }

    /**
     * Get the assignment associated with the package.
     */
    public function assignment()
    {
        return $this->hasOne(DeliveryAssignment::class, 'package_id', 'package_id');
    }

    /**
     * Get the driver through assignment
     */
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
     * Scopes for filtering
     */

    /**
     * Scope a query to only include packages with a specific status.
     */
    public function scopeStatus($query, $status)
    {
        return $query->where('package_status', $status);
    }

    /**
     * Scope a query to only include packages for a specific user.
     * --- MODIFIED ---
     */
    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope a query to search packages
     */
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
     * Scope for pending packages
     */
    public function scopePending($query)
    {
        return $query->whereIn('package_status', [
            self::STATUS_PENDING,
            self::STATUS_PROCESSING
        ]);
    }

    /**
     * Scope for active packages
     */
    public function scopeActive($query)
    {
        return $query->whereNotIn('package_status', [
            self::STATUS_DELIVERED,
            self::STATUS_CANCELLED,
            self::STATUS_RETURNED,
            self::STATUS_FAILED
        ]);
    }

    /**
     * Business Logic Methods
     */

    /**
     * Generate a unique package ID
     */
    public static function generatePackageId()
    {
        // Find the highest existing package number
        $lastPackage = DB::table('package')->where('package_id', 'like', 'P%')->orderBy('package_id', 'desc')->first();
        
        if ($lastPackage) {
            // Extract the number, increment it
            $number = (int)substr($lastPackage->package_id, 1);
            $nextNumber = $number + 1;
        } else {
            // Start from 1 if no packages exist
            $nextNumber = 1;
        }

        // Format it with leading zeros
        return 'P' . str_pad($nextNumber, 3, '0', STR_PAD_LEFT);
    }


    /**
     * Generate a unique tracking number
     */
    public static function generateTrackingNumber()
    {
        do {
            $trackingNumber = 'TRK' . date('ymd') . rand(100000, 999999);
        } while (self::where('tracking_number', $trackingNumber)->exists());

        return $trackingNumber;
    }

    /**
     * Calculate shipping cost based on weight and priority
     */
    public function calculateShippingCost()
    {
        $baseCost = 10.00;
        $weightCost = ($this->package_weight ?? 0) * 2.5;

        // Parse dimensions if in format "LxWxH"
        $dimensionCost = 0;
        if ($this->package_dimensions) {
            $dims = explode('x', strtolower($this->package_dimensions));
            if (count($dims) === 3) {
                $volume = array_product(array_map('floatval', $dims));
                // Add cost for large packages (volume > 10000 cm³)
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

    /**
     * Check if package can be edited
     */
    public function canBeEdited()
    {
        return in_array($this->package_status, [
            self::STATUS_PENDING,
            self::STATUS_PROCESSING
        ]);
    }

    /**
     * Check if package can be cancelled
     */
    public function canBeCancelled()
    {
        return !in_array($this->package_status, [
            self::STATUS_DELIVERED,
            self::STATUS_CANCELLED,
            self::STATUS_RETURNED
        ]);
    }

    /**
     * Get all available statuses
     */
    public static function getStatuses()
    {
        return [
            self::STATUS_PENDING => 'Pending',
            self::STATUS_PROCESSING => 'Processing',
            self::STATUS_IN_TRANSIT => 'In Transit',
            self::STATUS_OUT_FOR_DELIVERY => 'Out for Delivery',
            self::STATUS_DELIVERED => 'Delivered',
            self::STATUS_CANCELLED => 'Cancelled',
            self::STATUS_RETURNED => 'Returned',
            self::STATUS_FAILED => 'Failed Delivery',
        ];
    }

    /**
     * Get all available priorities
     */
    public static function getPriorities()
    {
        return [
            self::PRIORITY_STANDARD => 'Standard (5-7 days)',
            self::PRIORITY_EXPRESS => 'Express (2-3 days)',
            self::PRIORITY_URGENT => 'Urgent (1 day)',
        ];
    }

    /**
     * Update package status with validation
     */
    public function updateStatus($newStatus)
    {
        // Define allowed status transitions (State Pattern)
        $allowedTransitions = [
            self::STATUS_PENDING => [self::STATUS_PROCESSING, self::STATUS_CANCELLED],
            self::STATUS_PROCESSING => [self::STATUS_IN_TRANSIT, self::STATUS_CANCELLED],
            self::STATUS_IN_TRANSIT => [self::STATUS_OUT_FOR_DELIVERY, self::STATUS_RETURNED, self::STATUS_FAILED],
            self::STATUS_OUT_FOR_DELIVERY => [self::STATUS_DELIVERED, self::STATUS_FAILED, self::STATUS_RETURNED],
            self::STATUS_FAILED => [self::STATUS_IN_TRANSIT, self::STATUS_RETURNED],
            self::STATUS_DELIVERED => [],
            self::STATUS_CANCELLED => [],
            self::STATUS_RETURNED => []
        ];

        $currentStatus = $this->package_status;

        if (in_array($newStatus, $allowedTransitions[$currentStatus] ?? [])) {
            $this->package_status = $newStatus;

            // Update delivery timestamps
            if ($newStatus === self::STATUS_DELIVERED) {
                $this->actual_delivery = now();
            }

            return $this->save();
        }

        return false;
    }

    /**
     * Calculate estimated delivery date
     */
    public function calculateEstimatedDelivery()
    {
        $daysToAdd = [
            self::PRIORITY_STANDARD => 7,
            self::PRIORITY_EXPRESS => 3,
            self::PRIORITY_URGENT => 1
        ];

        $days = $daysToAdd[$this->priority ?? self::PRIORITY_STANDARD];

        // Skip weekends
        $date = now();
        while ($days > 0) {
            $date->addDay();
            if (!$date->isWeekend()) {
                $days--;
            }
        }

        return $date;
    }

    /**
     * Get current location/status description
     */
    public function getCurrentLocation()
    {
        $locations = [
            self::STATUS_PENDING => 'Package registered, awaiting pickup',
            self::STATUS_PROCESSING => 'At sorting facility',
            self::STATUS_IN_TRANSIT => 'In transit to destination',
            self::STATUS_OUT_FOR_DELIVERY => 'Out for delivery',
            self::STATUS_DELIVERED => 'Delivered successfully',
            self::STATUS_CANCELLED => 'Shipment cancelled',
            self::STATUS_RETURNED => 'Returned to sender',
            self::STATUS_FAILED => 'Delivery attempt failed'
        ];

        return $locations[$this->package_status] ?? 'Unknown';
    }
    

    /**
     * Get status color for UI
     */
    public function getStatusColor()
    {
        $colors = [
            self::STATUS_PENDING => 'warning',
            self::STATUS_PROCESSING => 'info',
            self::STATUS_IN_TRANSIT => 'primary',
            self::STATUS_OUT_FOR_DELIVERY => 'info',
            self::STATUS_DELIVERED => 'success',
            self::STATUS_CANCELLED => 'danger',
            self::STATUS_RETURNED => 'secondary',
            self::STATUS_FAILED => 'danger'
        ];

        return $colors[$this->package_status] ?? 'secondary';
    }

    /**
     * Format package details for display
     */
    public function getFormattedDetails()
    {
        return [
            'id' => $this->package_id,
            'tracking' => $this->tracking_number,
            'status' => $this->package_status,
            'status_text' => self::getStatuses()[$this->package_status] ?? 'Unknown',
            'status_color' => $this->getStatusColor(),
            'location' => $this->getCurrentLocation(),
            'weight' => $this->package_weight . ' kg',
            'dimensions' => $this->package_dimensions,
            'cost' => '$' . number_format($this->shipping_cost ?? 0, 2),
            'can_edit' => $this->canBeEdited(),
            'can_cancel' => $this->canBeCancelled()
        ];
    }
}