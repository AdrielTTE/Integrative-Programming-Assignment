<?php

namespace App\Http\Controllers\AdminControllers;

use App\Http\Controllers\Controller;
use App\Models\Package;
use App\Models\User;
use App\Models\AdminAuditLog;
use App\Services\PackageService;
use App\Services\Api\PackageService as ApiPackageService;
use App\Traits\Auditable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Observers\PackageSubject;
use App\Observers\CustomerObserver;
use Illuminate\Support\Facades\Http;

class AdminPackageController extends Controller
{
    use Auditable;  // Add the trait

    protected string $baseUrl;

    protected PackageService $packageService;
    protected ApiPackageService $apiPackageService;

    public function __construct(
        PackageService $packageService,
        ApiPackageService $apiPackageService
    ) {
        $this->packageService = $packageService;
        $this->apiPackageService = $apiPackageService;
        $this->baseUrl = config('services.api.base_url', 'http://localhost:8001/api');
    }

    /**
     * Display all packages with filtering
     */
    public function index(Request $request)
    {
        try {
            $this->audit(
                'view_list',
                'packages',
                'all',
                'Admin viewed packages list with filters',
                null,
                ['filters' => $request->all()]
            );

            $validated = $request->validate([
                'search' => 'nullable|string|max:100',
                'status' => 'nullable|string',
                'date_from' => 'nullable|date|before_or_equal:today',
                'date_to' => 'nullable|date|after_or_equal:date_from',
                'sort_by' => 'nullable|in:created_at,updated_at,package_id,package_status,priority',
                'sort_order' => 'nullable|in:asc,desc',
                'per_page' => 'nullable|integer|min:10|max:100'
            ]);

            $query = Package::with(['user', 'delivery.driver', 'assignment']);

            if (!empty($validated['search'])) {
                $search = '%' . $validated['search'] . '%';
                $query->where(function ($q) use ($search) {
                    $q->where('tracking_number', 'LIKE', $search)
                      ->orWhere('package_id', 'LIKE', $search)
                      ->orWhereHas('user', function ($userQuery) use ($search) {
                        $userQuery->where('username', 'LIKE', $search)
                                  ->orWhere('email', 'LIKE', $search);
                      });
                });
            }

            if (!empty($validated['status'])) {
                $query->where('package_status', $validated['status']);
            }

            if (!empty($validated['date_from'])) {
                $query->whereDate('created_at', '>=', $validated['date_from']);
            }

            if (!empty($validated['date_to'])) {
                $query->whereDate('created_at', '<=', $validated['date_to']);
            }

            // Apply sorting
            $sortBy = $validated['sort_by'] ?? 'created_at';
            $sortOrder = $validated['sort_order'] ?? 'desc';
            $query->orderBy($sortBy, $sortOrder);

            // Paginate results
            $packages = $query->paginate($validated['per_page'] ?? 20);
            $statistics = $this->getPackageStatistics();
            // Get statistics

            $totalPackages = $this->packageService->getTotalPackages();

            // Get statuses for filter dropdown
            $statuses = Package::getStatuses();

            return view('admin.packages.index', compact('packages', 'statistics', 'totalPackages','statuses'));

        } catch (\Exception $e) {
            $this->auditError(
                'view_list',
                'packages',
                'all',
                $e->getMessage()
            );

            return back()->with('error', 'An error occurred while loading packages.');
        }
    }

    /**
     * Show detailed package information
     */
    public function show(string $packageId)
    {
        try {
            // Validate package ID format
            if (!preg_match('/^P\d{3,}$/', $packageId)) {
                abort(404, 'Invalid package ID format');
            }

            $package = Package::with([
                'user',
                'delivery.driver',
                'assignment'
            ])->findOrFail($packageId);

            // Log viewing package details
            $this->audit(
                'view',
                'package',
                $packageId,
                "Admin viewed details of package {$packageId}"
            );

            // Get package state information
            $currentState = $package->getState();
            $stateInfo = [
                'name' => $currentState->getStatusName(),
                'color' => $currentState->getStatusColor(),
                'location' => $currentState->getCurrentLocation(),
                'can_edit' => $currentState->canBeEdited(),
                'can_cancel' => $currentState->canBeCancelled(),
                'can_assign' => $currentState->canBeAssigned(),
                'allowed_transitions' => $currentState->getAllowedTransitions()
            ];

            // Get package history
            $history = $this->packageService->getPackageHistory($packageId);

            // Get audit logs for this package
            $auditLogs = $this->getAuditLogsFor('package', $packageId, 20);

            // Get available drivers
            $availableDrivers = User::where('user_id', 'like', 'D%')->get();

            // Get all possible statuses
            $statuses = Package::getStatuses();

            return view('admin.packages.show', compact(
                'package',
                'stateInfo',
                'history',
                'auditLogs',
                'availableDrivers',
                'statuses'
            ));

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            $this->auditError(
                'view',
                'package',
                $packageId,
                'Package not found'
            );

            return redirect()->route('admin.packages.index')
                           ->with('error', 'Package not found.');
        }
    }

    /**
     * Update package with audit logging
     */
    public function update(Request $request, string $packageId)
    {
        DB::beginTransaction();

        try {
            // Find the package
            $package = Package::where('package_id', $packageId)->firstOrFail();

            // Store original values for audit
            $originalData = $package->toArray();

            // Handle action-based updates
            if ($request->has('action')) {
                $result = $this->handlePackageAction($request->input('action'), $package, $originalData);
                DB::commit();
                return $result;
            }

            // Validate the incoming data
            $validated = $request->validate([
                'package_status' => 'nullable|string',
                'package_contents' => 'nullable|string|max:500',
                'package_weight' => 'nullable|numeric|min:0.01|max:1000',
                'package_dimensions' => 'nullable|string|max:50',
                'priority' => 'nullable|in:standard,express,urgent',
                'shipping_cost' => 'nullable|numeric|min:0|max:10000',
                'estimated_delivery' => 'nullable|date',
                'actual_delivery' => 'nullable|date',
                'sender_address' => 'nullable|string|max:500',
                'recipient_address' => 'nullable|string|max:500',
                'notes' => 'nullable|string|max:1000'
            ]);

            // Remove null values
            $dataToUpdate = array_filter($validated, function($value) {
                return $value !== null && $value !== '';
            });

            // If status is being updated, normalize it
            if (isset($dataToUpdate['package_status'])) {
                $dataToUpdate['package_status'] = strtolower($dataToUpdate['package_status']);
            }

            // Update the package
            if (!empty($dataToUpdate)) {
                foreach ($dataToUpdate as $key => $value) {
                    $package->{$key} = $value;
                }

                $package->save();

                //For Observer
if ($package->wasChanged('package_status')) {
    $package->load('customer'); // ensure customer is available

    $subject = new PackageSubject($package);
    $observer = new CustomerObserver($package->customer);
    $subject->addObserver($observer);
    $subject->notifyObserver();

}


                // Log successful update with changes
                $this->auditPackageAction('update', $packageId, [
                    'old' => array_intersect_key($originalData, $dataToUpdate),
                    'new' => $dataToUpdate
                ]);

                DB::commit();

                return redirect()->route('admin.packages.show', $packageId)
                               ->with('success', 'Package updated successfully!');
            }

            DB::commit();
            return redirect()->route('admin.packages.show', $packageId)
                           ->with('info', 'No changes were made.');

        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollback();

            $this->auditError(
                'update',
                'package',
                $packageId,
                'Validation failed: ' . json_encode($e->errors()),
                $request->all()
            );

            return back()->withErrors($e->errors())->withInput();

        } catch (\Exception $e) {
            DB::rollback();

            $this->auditError(
                'update',
                'package',
                $packageId,
                $e->getMessage(),
                $request->all()
            );

            return back()->with('error', 'Failed to update package: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Handle special package actions with audit logging
     */
    private function handlePackageAction(string $action, Package $package, array $originalData)
    {
        try {
            $oldStatus = $package->package_status;

            switch ($action) {
                case 'process':
                    $package->package_status = 'processing';
                    $package->save();
                    $message = 'Package marked as processing.';
                    break;

                case 'cancel':
                    $package->package_status = 'cancelled';
                    $package->save();
                    $message = 'Package cancelled successfully.';
                    break;

                case 'deliver':
                    $package->package_status = 'delivered';
                    $package->actual_delivery = now();
                    $package->save();
                    $message = 'Package marked as delivered.';
                    break;

                case 'return':
                    $package->package_status = 'returned';
                    $package->save();
                    $message = 'Package marked as returned.';
                    break;

                default:
                    throw new \Exception("Unknown action: {$action}");
            }

            // Log the action
            $this->auditPackageAction($action, $package->package_id, [
                'old' => ['package_status' => $oldStatus],
                'new' => ['package_status' => $package->package_status]
            ]);

            return redirect()->route('admin.packages.show', $package->package_id)
                           ->with('success', $message);

        } catch (\Exception $e) {
            $this->auditError(
                $action,
                'package',
                $package->package_id,
                $e->getMessage()
            );

            return back()->with('error', 'Action failed: ' . $e->getMessage());
        }
    }
    private function getPackageStatistics(){
        $response = Http::get("{$this->baseUrl}/package/getPackageStatistics");

    if ($response->failed()) {
        return 0;
    }

    return $response->json();

    }

    /**
     * Delete package with audit logging
     */
    public function destroy(string $packageId)
    {
        DB::beginTransaction();

        try {
            $package = Package::findOrFail($packageId);

            // Store package data before deletion
            $packageData = $package->toArray();

            // Check if package can be deleted
            $status = strtolower($package->package_status);
            if (in_array($status, ['delivered', 'in_transit'])) {
                throw new \Exception('Cannot delete packages that are delivered or in transit.');
            }

            $package->delete();

            // Log deletion
            $this->auditPackageAction('delete', $packageId, [
                'old' => $packageData,
                'new' => null
            ]);

            DB::commit();

            return redirect()->route('admin.packages.index')
                           ->with('success', 'Package deleted successfully.');

        } catch (\Exception $e) {
            DB::rollback();

            $this->auditError(
                'delete',
                'package',
                $packageId,
                $e->getMessage()
            );

            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * View audit logs
     */
   public function auditLogs(Request $request)
{
    $query = AdminAuditLog::with('admin')->orderBy('created_at', 'desc');

    // Apply filters if provided
    if ($request->has('admin_id') && !empty($request->admin_id)) {
        $query->where('admin_id', $request->admin_id);
    }

    if ($request->has('action') && !empty($request->action)) {
        $query->where('action', $request->action);
    }

    if ($request->has('target_type') && !empty($request->target_type)) {
        $query->where('target_type', $request->target_type);
    }

    if ($request->has('date_from') && !empty($request->date_from)) {
        $query->whereDate('created_at', '>=', $request->date_from);
    }

    if ($request->has('date_to') && !empty($request->date_to)) {
        $query->whereDate('created_at', '<=', $request->date_to);
    }

    if ($request->has('status') && !empty($request->status)) {
        $query->where('status', $request->status);
    }

    if ($request->has('search') && !empty($request->search)) {
        $search = $request->search;
        $query->where(function ($q) use ($search) {
            $q->where('description', 'like', "%{$search}%")
              ->orWhere('target_id', 'like', "%{$search}%")
              ->orWhere('admin_username', 'like', "%{$search}%");
        });
    }

    // Order by created_at descending and paginate
    $logs = $query->orderBy('created_at', 'desc')->paginate(20);

    return view('admin.audit-logs', compact('logs'));
}



    // Alternative safer implementation using Laravel's when() method
    public function auditLogsAlternative(Request $request)
    {
        $logs = AdminAuditLog::query()
            ->when($request->filled('admin_id'), function ($query) use ($request) {
                return $query->where('admin_id', $request->admin_id);
            })
            ->when($request->filled('action'), function ($query) use ($request) {
                return $query->where('action', $request->action);
            })
            ->when($request->filled('target_type'), function ($query) use ($request) {
                return $query->where('target_type', $request->target_type);
            })
            ->when($request->filled('date_from'), function ($query) use ($request) {
                return $query->whereDate('created_at', '>=', $request->date_from);
            })
            ->when($request->filled('date_to'), function ($query) use ($request) {
                return $query->whereDate('created_at', '<=', $request->date_to);
            })
            ->when($request->filled('status'), function ($query) use ($request) {
                return $query->where('status', $request->status);
            })
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->search;
                return $query->where(function ($q) use ($search) {
                    $q->where('description', 'like', "%{$search}%")
                      ->orWhere('target_id', 'like', "%{$search}%")
                      ->orWhere('admin_username', 'like', "%{$search}%");
                });
            })
            ->orderBy('created_at', 'desc')
            ->paginate(20)
            ->withQueryString();

        return view('admin.audit-logs', compact('logs'));
    }



    public function exportAuditLogs(Request $request)
{
    // Build query with same filters as the main listing
    $query = AdminAuditLog::query();

    // Apply filters
    if ($request->filled('admin_id')) {
        $query->where('admin_id', $request->admin_id);
    }

    if ($request->filled('action')) {
        $query->where('action', $request->action);
    }

    if ($request->filled('target_type')) {
        $query->where('target_type', $request->target_type);
    }

    if ($request->filled('status')) {
        $query->where('status', $request->status);
    }

    if ($request->filled('date_from')) {
        $query->whereDate('created_at', '>=', $request->date_from);
    }

    if ($request->filled('date_to')) {
        $query->whereDate('created_at', '<=', $request->date_to);
    }

    if ($request->filled('search')) {
        $search = $request->search;
        $query->where(function ($q) use ($search) {
            $q->where('description', 'like', "%{$search}%")
              ->orWhere('target_id', 'like', "%{$search}%")
              ->orWhere('admin_username', 'like', "%{$search}%");
        });
    }

    // Get all filtered logs
    $logs = $query->orderBy('created_at', 'desc')->get();

    // Generate CSV filename
    $filename = 'audit_logs_' . date('Y-m-d_H-i-s') . '.csv';

    // Set headers for CSV download
    $headers = [
        'Content-Type' => 'text/csv',
        'Content-Disposition' => "attachment; filename=\"$filename\"",
        'Pragma' => 'no-cache',
        'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
        'Expires' => '0'
    ];

    // Create CSV callback
    $callback = function() use ($logs) {
        $file = fopen('php://output', 'w');

        // Add UTF-8 BOM for Excel compatibility
        fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));

        // Add CSV headers
        fputcsv($file, [
            'Timestamp',
            'Admin ID',
            'Admin Username',
            'Action',
            'Target Type',
            'Target ID',
            'Description',
            'Status',
            'IP Address'
        ]);

        // Add data rows
        foreach ($logs as $log) {
            fputcsv($file, [
                $log->created_at->format('Y-m-d H:i:s'),
                $log->admin_id,
                $log->admin_username,
                $log->action,
                $log->target_type,
                $log->target_id,
                $log->description ?: 'N/A',
                $log->status,
                $log->ip_address
            ]);
        }

        fclose($file);
    };

    return response()->stream($callback, 200, $headers);
}



}
