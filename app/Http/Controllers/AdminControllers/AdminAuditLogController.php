<?php

namespace App\Http\Controllers\AdminControllers;

use App\Http\Controllers\Controller;
use App\Models\AdminAuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminAuditLogController extends Controller
{
    /**
     * Display audit logs with filtering
     */
    public function index(Request $request)
    {
        // Build the query using Laravel's when() method for cleaner code
        $logs = AdminAuditLog::query()
            ->when($request->filled('admin_id'), fn($q) => 
                $q->where('admin_id', $request->admin_id)
            )
            ->when($request->filled('action'), fn($q) => 
                $q->where('action', $request->action)
            )
            ->when($request->filled('target_type'), fn($q) => 
                $q->where('target_type', $request->target_type)
            )
            ->when($request->filled('status'), fn($q) => 
                $q->where('status', $request->status)
            )
            ->when($request->filled('date_from'), fn($q) => 
                $q->whereDate('created_at', '>=', $request->date_from)
            )
            ->when($request->filled('date_to'), fn($q) => 
                $q->whereDate('created_at', '<=', $request->date_to)
            )
            ->when($request->filled('search'), function ($q) use ($request) {
                $search = $request->search;
                $q->where(function ($query) use ($search) {
                    $query->where('description', 'like', "%{$search}%")
                          ->orWhere('target_id', 'like', "%{$search}%")
                          ->orWhere('admin_username', 'like', "%{$search}%")
                          ->orWhere('error_message', 'like', "%{$search}%");
                });
            })
            ->orderBy('created_at', 'desc')
            ->paginate(20)
            ->withQueryString(); // This keeps filter parameters in pagination links
        
        return view('admin.audit-logs', compact('logs'));
    }

    /**
     * Show detailed view of a single audit log
     */
    public function show($id)
    {
        $log = AdminAuditLog::findOrFail($id);
        
        // Decode JSON fields for better display
        $log->old_values = json_decode($log->old_values, true);
        $log->new_values = json_decode($log->new_values, true);
        $log->metadata = json_decode($log->metadata, true);
        
        return response()->json($log);
    }

    /**
     * Export audit logs to CSV
     */
    public function export(Request $request)
    {
        // Apply the same filters as index method
        $query = AdminAuditLog::query()
            ->when($request->filled('admin_id'), fn($q) => 
                $q->where('admin_id', $request->admin_id)
            )
            ->when($request->filled('action'), fn($q) => 
                $q->where('action', $request->action)
            )
            ->when($request->filled('target_type'), fn($q) => 
                $q->where('target_type', $request->target_type)
            )
            ->when($request->filled('status'), fn($q) => 
                $q->where('status', $request->status)
            )
            ->when($request->filled('date_from'), fn($q) => 
                $q->whereDate('created_at', '>=', $request->date_from)
            )
            ->when($request->filled('date_to'), fn($q) => 
                $q->whereDate('created_at', '<=', $request->date_to)
            )
            ->when($request->filled('search'), function ($q) use ($request) {
                $search = $request->search;
                $q->where(function ($query) use ($search) {
                    $query->where('description', 'like', "%{$search}%")
                          ->orWhere('target_id', 'like', "%{$search}%")
                          ->orWhere('admin_username', 'like', "%{$search}%");
                });
            });

        $logs = $query->orderBy('created_at', 'desc')->get();
        
        $filename = 'audit_logs_' . now()->format('Y-m-d_H-i-s') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0'
        ];
        
        $callback = function() use ($logs) {
            $file = fopen('php://output', 'w');
            
            // Write BOM for Excel UTF-8 compatibility
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            
            // CSV headers
            fputcsv($file, [
                'Timestamp',
                'Admin ID',
                'Admin Username',
                'Action',
                'Target Type',
                'Target ID',
                'Description',
                'Status',
                'IP Address',
                'User Agent',
                'Method',
                'URL'
            ]);
            
            // CSV data rows
            foreach ($logs as $log) {
                fputcsv($file, [
                    $log->created_at->format('Y-m-d H:i:s'),
                    $log->admin_id,
                    $log->admin_username,
                    $log->action,
                    $log->target_type,
                    $log->target_id,
                    $log->description ?? 'N/A',
                    $log->status,
                    $log->ip_address,
                    substr($log->user_agent ?? '', 0, 50), // Truncate long user agents
                    $log->method ?? '',
                    $log->url ?? ''
                ]);
            }
            
            fclose($file);
        };
        
        return response()->stream($callback, 200, $headers);
    }

    /**
     * Get statistics for dashboard
     */
    public function statistics(Request $request)
    {
        $dateFrom = $request->filled('date_from') 
            ? $request->date_from 
            : now()->subDays(7)->startOfDay();
            
        $dateTo = $request->filled('date_to') 
            ? $request->date_to 
            : now()->endOfDay();
        
        // Action distribution
        $actionStats = AdminAuditLog::whereBetween('created_at', [$dateFrom, $dateTo])
            ->select('action', DB::raw('COUNT(*) as count'))
            ->groupBy('action')
            ->orderBy('count', 'desc')
            ->get();
        
        // Top admins by activity
        $topAdmins = AdminAuditLog::whereBetween('created_at', [$dateFrom, $dateTo])
            ->select('admin_id', 'admin_username', DB::raw('COUNT(*) as total_actions'))
            ->groupBy('admin_id', 'admin_username')
            ->orderBy('total_actions', 'desc')
            ->limit(10)
            ->get();
        
        // Success/Failure rate
        $statusStats = AdminAuditLog::whereBetween('created_at', [$dateFrom, $dateTo])
            ->select('status', DB::raw('COUNT(*) as count'))
            ->groupBy('status')
            ->get()
            ->pluck('count', 'status');
        
        $successRate = $statusStats->sum() > 0 
            ? round(($statusStats->get('success', 0) / $statusStats->sum()) * 100, 2)
            : 0;
        
        // Activity by hour (last 24 hours)
        $hourlyActivity = AdminAuditLog::where('created_at', '>=', now()->subDay())
            ->select(DB::raw('HOUR(created_at) as hour'), DB::raw('COUNT(*) as count'))
            ->groupBy('hour')
            ->orderBy('hour')
            ->get()
            ->pluck('count', 'hour');
        
        // Fill missing hours with zeros
        $hourlyData = collect(range(0, 23))->map(function ($hour) use ($hourlyActivity) {
            return $hourlyActivity->get($hour, 0);
        });
        
        // Target type distribution
        $targetTypes = AdminAuditLog::whereBetween('created_at', [$dateFrom, $dateTo])
            ->select('target_type', DB::raw('COUNT(*) as count'))
            ->groupBy('target_type')
            ->orderBy('count', 'desc')
            ->get();
        
        return response()->json([
            'date_range' => [
                'from' => $dateFrom,
                'to' => $dateTo
            ],
            'action_stats' => $actionStats,
            'top_admins' => $topAdmins,
            'success_rate' => $successRate,
            'status_stats' => $statusStats,
            'hourly_activity' => $hourlyData,
            'target_types' => $targetTypes,
            'total_logs' => AdminAuditLog::whereBetween('created_at', [$dateFrom, $dateTo])->count()
        ]);
    }

    /**
     * Clean up old audit logs
     */
    public function cleanup(Request $request)
    {
        $request->validate([
            'days' => 'required|integer|min:30|max:365',
            'confirm' => 'required|accepted'
        ]);
        
        $cutoffDate = now()->subDays($request->days);
        
        // Count logs to be deleted
        $count = AdminAuditLog::where('created_at', '<', $cutoffDate)->count();
        
        if ($count > 0) {
            // Delete old logs
            AdminAuditLog::where('created_at', '<', $cutoffDate)->delete();
            
            // Log this cleanup action
            AdminAuditLog::logSuccess(
                'cleanup',
                'system',
                'audit_logs',
                "Deleted {$count} audit logs older than {$request->days} days"
            );
            
            return redirect()->route('admin.audit.logs')
                ->with('success', "Successfully deleted {$count} old audit logs.");
        }
        
        return redirect()->route('admin.audit.logs')
            ->with('info', 'No old audit logs to delete.');
    }
}