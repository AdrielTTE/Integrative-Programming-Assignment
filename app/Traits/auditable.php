<?php

namespace App\Traits;

use App\Models\AdminAuditLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

trait Auditable
{
    /**
     * Log an activity to the audit log
     */
    protected function audit(
        string $action,
        string $targetType,
        string $targetId,
        string $description = null,
        array $oldData = null,
        array $newData = null
    ): void {
        try {
            AdminAuditLog::logSuccess(
                $action,
                $targetType,
                $targetId,
                $description,
                $oldData,
                $newData
            );
        } catch (\Exception $e) {
            // If audit logging fails, log to Laravel log file
            Log::error('Audit logging failed', [
                'action' => $action,
                'target' => "{$targetType}:{$targetId}",
                'error' => $e->getMessage()
            ]);
        }
    }
    
    /**
     * Log a failed action to the audit log
     */
    protected function auditError(
        string $action,
        string $targetType,
        string $targetId,
        string $error,
        array $attemptedData = null
    ): void {
        try {
            AdminAuditLog::logError(
                $action,
                $targetType,
                $targetId,
                $error,
                $attemptedData
            );
        } catch (\Exception $e) {
            Log::error('Audit error logging failed', [
                'action' => $action,
                'target' => "{$targetType}:{$targetId}",
                'original_error' => $error,
                'logging_error' => $e->getMessage()
            ]);
        }
    }
    
    /**
     * Log a package action
     */
    protected function auditPackageAction(
        string $action,
        string $packageId,
        array $changes = null
    ): void {
        $description = match($action) {
            'create' => "Created new package {$packageId}",
            'update' => "Updated package {$packageId}",
            'delete' => "Deleted package {$packageId}",
            'process' => "Processed package {$packageId}",
            'cancel' => "Cancelled package {$packageId}",
            'deliver' => "Marked package {$packageId} as delivered",
            'assign' => "Assigned package {$packageId} to driver",
            'return' => "Marked package {$packageId} as returned",
            default => "{$action} package {$packageId}"
        };
        
        $this->audit(
            $action,
            'package',
            $packageId,
            $description,
            $changes['old'] ?? null,
            $changes['new'] ?? null
        );
    }
    
    /**
     * Get audit logs for a specific target
     */
    protected function getAuditLogsFor(string $targetType, string $targetId, int $limit = 10)
    {
        return AdminAuditLog::where('target_type', $targetType)
                            ->where('target_id', $targetId)
                            ->orderBy('created_at', 'desc')
                            ->limit($limit)
                            ->get();
    }
    
    /**
     * Get recent admin activities
     */
    protected function getRecentAdminActivities(int $limit = 50)
    {
        return AdminAuditLog::with('admin')
                            ->orderBy('created_at', 'desc')
                            ->limit($limit)
                            ->get();
    }
}