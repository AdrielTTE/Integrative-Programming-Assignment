<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class AdminAuditLog extends Model
{
    protected $table = 'admin_audit_log';
    
    protected $fillable = [
        'admin_id',
        'admin_username',
        'action',
        'target_type',
        'target_id',
        'description',
        'old_values',
        'new_values',
        'metadata',
        'ip_address',
        'user_agent',
        'method',
        'url',
        'status',
        'error_message'
    ];
    
    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
        'metadata' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];
    
    /**
     * Create a new audit log entry
     */
    public static function log(
        string $action, 
        string $targetType, 
        string $targetId, 
        array $data = []
    ): self {
        $admin = Auth::user();
        
        return self::create([
            'admin_id' => $admin->user_id ?? 'SYSTEM',
            'admin_username' => $admin->username ?? 'System',
            'action' => $action,
            'target_type' => $targetType,
            'target_id' => $targetId,
            'description' => $data['description'] ?? null,
            'old_values' => $data['old_values'] ?? null,
            'new_values' => $data['new_values'] ?? null,
            'metadata' => $data['metadata'] ?? null,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'method' => request()->method(),
            'url' => request()->fullUrl(),
            'status' => $data['status'] ?? 'success',
            'error_message' => $data['error_message'] ?? null
        ]);
    }
    
    /**
     * Log a successful action
     */
    public static function logSuccess(
        string $action,
        string $targetType,
        string $targetId,
        string $description = null,
        array $oldValues = null,
        array $newValues = null
    ): self {
        return self::log($action, $targetType, $targetId, [
            'description' => $description,
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'status' => 'success'
        ]);
    }
    
    /**
     * Log a failed action
     */
    public static function logError(
        string $action,
        string $targetType,
        string $targetId,
        string $errorMessage,
        array $attemptedData = null
    ): self {
        return self::log($action, $targetType, $targetId, [
            'description' => "Failed to {$action} {$targetType}",
            'new_values' => $attemptedData,
            'status' => 'failed',
            'error_message' => $errorMessage
        ]);
    }
    
    /**
     * Relationships
     */
    public function admin()
    {
        return $this->belongsTo(User::class, 'admin_id', 'user_id');
    }
    
    /**
     * Scopes for filtering
     */
    public function scopeByAdmin($query, $adminId)
    {
        return $query->where('admin_id', $adminId);
    }
    
    public function scopeByAction($query, $action)
    {
        return $query->where('action', $action);
    }
    
    public function scopeByTargetType($query, $targetType)
    {
        return $query->where('target_type', $targetType);
    }
    
    public function scopeByDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('created_at', [$startDate, $endDate]);
    }
    
    public function scopeSuccessful($query)
    {
        return $query->where('status', 'success');
    }
    
    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }
}