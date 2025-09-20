<?php

namespace App\Http\Controllers\AdminControllers;

use App\Http\Controllers\Controller;
use App\Models\Package;
use App\Models\User;
use App\Services\PackageService;
use App\Services\Api\PackageService as ApiPackageService;
use App\Http\Requests\AdminPackageUpdateRequest;
use App\Factories\PackageStateFactory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Http;
use App\Http\Controllers\AdminControllers\AdminPackageController;

class AdminPackageController extends Controller
{
    protected PackageService $packageService;
    protected ApiPackageService $apiPackageService;

    public function __construct(
        PackageService $packageService, 
        ApiPackageService $apiPackageService
    ) {
        $this->packageService = $packageService;
        $this->apiPackageService = $apiPackageService;
    }

    /**
     * Display all packages with filtering and security
     */
    public function index(Request $request)
    {
        try {
            // Input validation and sanitization
            $validated = $request->validate([
                'search' => 'nullable|string|max:100|regex:/^[a-zA-Z0-9\s\-]+$/',
                'status' => 'nullable|in:' . implode(',', array_keys(Package::getStatuses())),
                'customer_id' => 'nullable|string|regex:/^C\d{3,}$/|exists:user,user_id',
                'date_from' => 'nullable|date|before_or_equal:today',
                'date_to' => 'nullable|date|after_or_equal:date_from',
                'sort_by' => 'nullable|in:created_at,updated_at,package_id,package_status,priority',
                'sort_order' => 'nullable|in:asc,desc',
                'per_page' => 'nullable|integer|min:10|max:100'
            ]);

            // Build query with eager loading
            $query = Package::with(['user', 'delivery.driver', 'assignment']);

            // Apply search filter
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

            // Apply filters
            if (!empty($validated['status'])) {
                $query->where('package_status', $validated['status']);
            }

            if (!empty($validated['customer_id'])) {
                $query->where('user_id', $validated['customer_id']);
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

            // Get statistics
            $statistics = $this->getPackageStatistics();

            // Get statuses for filter dropdown
            $statuses = Package::getStatuses();

            // Log admin access
            Log::channel('admin')->info('Admin viewed packages list', [
                'admin_id' => Auth::id(),
                'filters' => $validated,
                'results_count' => $packages->total()
            ]);

            return view('admin.packages.index', compact('packages', 'statistics', 'statuses'));

        } catch (\Exception $e) {
            Log::error('Error in admin package index', [
                'error' => $e->getMessage(),
                'admin_id' => Auth::id()
            ]);
            
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
                'assignment',
                'delivery.proofOfDelivery'
            ])->findOrFail($packageId);

            // Get package state information using State Pattern
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

            // Get available drivers for assignment
            $availableDrivers = User::where('user_id', 'like', 'D%')->get();

            // Get all possible statuses
            $statuses = Package::getStatuses();

            // Log viewing
            Log::channel('admin')->info('Admin viewed package details', [
                'admin_id' => Auth::id(),
                'package_id' => $packageId,
                'customer_id' => $package->user_id
            ]);

            return view('admin.packages.show', compact(
                'package', 
                'stateInfo', 
                'history', 
                'availableDrivers',
                'statuses'
            ));

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return redirect()->route('admin.packages.index')
                           ->with('error', 'Package not found.');
        }
    }

    /**
     * Update package with state validation
     */
    public function update(AdminPackageUpdateRequest $request, string $packageId)
    {
        DB::beginTransaction();
        
        try {
            $package = Package::findOrFail($packageId);
            $validated = $request->validated();
            
            // Rate limiting per admin
            $rateLimitKey = 'admin_update_' . Auth::id();
            if (!RateLimiter::attempt($rateLimitKey, 10, function() {}, 60)) {
                throw new \Exception('Too many update attempts. Please wait before trying again.');
            }

            // Store original state for audit
            $originalData = $package->toArray();

            // Handle state transitions using State Pattern
            if (isset($validated['action'])) {
                $currentState = $package->getState();
                
                switch ($validated['action']) {
                    case 'process':
                        if ($currentState->canTransitionTo(Package::STATUS_PROCESSING)) {
                            $package->process($validated);
                        } else {
                            throw new \Exception("Cannot process package in {$currentState->getStatusName()} state");
                        }
                        break;
                        
                    case 'cancel':
                        if ($currentState->canBeCancelled()) {
                            $package->cancel(Auth::user());
                        } else {
                            throw new \Exception("Cannot cancel package in {$currentState->getStatusName()} state");
                        }
                        break;
                        
                    case 'assign':
                        if (!isset($validated['driver_id'])) {
                            throw new \Exception('Driver ID required for assignment');
                        }
                        if ($currentState->canBeAssigned()) {
                            $package->assign($validated['driver_id']);
                        } else {
                            throw new \Exception("Cannot assign package in {$currentState->getStatusName()} state");
                        }
                        break;
                        
                    case 'deliver':
                        if ($currentState->canTransitionTo(Package::STATUS_DELIVERED)) {
                            $package->deliver($validated['proof_data'] ?? []);
                        } else {
                            throw new \Exception("Cannot deliver package in {$currentState->getStatusName()} state");
                        }
                        break;
                        
                    case 'return':
                        if ($currentState->canTransitionTo(Package::STATUS_RETURNED)) {
                            $newState = PackageStateFactory::createByStatus(Package::STATUS_RETURNED, $package);
                            $package->setState($newState);
                            $package->save();
                        } else {
                            throw new \Exception("Cannot return package in {$currentState->getStatusName()} state");
                        }
                        break;
                }
            }

            // Direct status update
            if (isset($validated['package_status']) && !isset($validated['action'])) {
                $currentState = $package->getState();
                if ($currentState->canTransitionTo($validated['package_status'])) {
                    $newState = PackageStateFactory::createByStatus($validated['package_status'], $package);
                    $package->setState($newState);
                } else {
                    throw new \Exception("Invalid status transition");
                }
            }

            // Update other fields
            $allowedFields = ['priority', 'notes', 'estimated_delivery', 'sender_address', 'recipient_address'];
            foreach ($allowedFields as $field) {
                if (isset($validated[$field])) {
                    $package->$field = $validated[$field];
                }
            }

            $package->save();

            // Create audit log
            DB::table('admin_audit_log')->insert([
                'admin_id' => Auth::id(),
                'action' => 'update_package',
                'target_type' => 'package',
                'target_id' => $packageId,
                'old_values' => json_encode($originalData),
                'new_values' => json_encode($package->toArray()),
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'created_at' => now()
            ]);

            DB::commit();

            Cache::tags(['packages', "package_{$packageId}"])->flush();

            return redirect()->route('admin.packages.show', $packageId)
                           ->with('success', 'Package updated successfully.');

        } catch (\Exception $e) {
            DB::rollback();
            
            Log::error('Error updating package', [
                'package_id' => $packageId,
                'admin_id' => Auth::id(),
                'error' => $e->getMessage()
            ]);

            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    /**
     * Delete package with soft delete
     */
    public function destroy(string $packageId)
    {
        DB::beginTransaction();
        
        try {
            $package = Package::findOrFail($packageId);
            
            // Check if package can be deleted
            if (in_array($package->package_status, [Package::STATUS_DELIVERED, Package::STATUS_IN_TRANSIT])) {
                throw new \Exception('Cannot delete packages that are delivered or in transit.');
            }

            $packageData = $package->toArray();
            $package->delete();

            // Create audit log
            DB::table('admin_audit_log')->insert([
                'admin_id' => Auth::id(),
                'action' => 'delete_package',
                'target_type' => 'package',
                'target_id' => $packageId,
                'old_values' => json_encode($packageData),
                'new_values' => json_encode(['deleted_at' => now()]),
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'created_at' => now()
            ]);

            DB::commit();

            Cache::tags(['packages', "package_{$packageId}"])->flush();

            return redirect()->route('admin.packages.index')
                           ->with('success', 'Package deleted successfully.');

        } catch (\Exception $e) {
            DB::rollback();
            
            Log::error('Error deleting package', [
                'package_id' => $packageId,
                'admin_id' => Auth::id(),
                'error' => $e->getMessage()
            ]);

            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Bulk operations
     */
    public function bulkAction(Request $request)
    {
        $validated = $request->validate([
            'package_ids' => 'required|array|min:1|max:50',
            'package_ids.*' => 'required|string|regex:/^P\d{3,}$/|exists:package,package_id',
            'action' => 'required|in:delete,update_status,assign_driver',
            'value' => 'nullable|string|max:255'
        ]);

        DB::beginTransaction();

        try {
            $successCount = 0;
            $failedCount = 0;
            $errors = [];

            foreach ($validated['package_ids'] as $packageId) {
                try {
                    $package = Package::findOrFail($packageId);
                    $currentState = $package->getState();
                    
                    switch ($validated['action']) {
                        case 'delete':
                            if (!in_array($package->package_status, [Package::STATUS_DELIVERED, Package::STATUS_IN_TRANSIT])) {
                                $package->delete();
                                $successCount++;
                            } else {
                                $failedCount++;
                                $errors[] = "Package {$packageId} cannot be deleted";
                            }
                            break;
                            
                        case 'update_status':
                            if ($currentState->canTransitionTo($validated['value'])) {
                                $newState = PackageStateFactory::createByStatus($validated['value'], $package);
                                $package->setState($newState);
                                $package->save();
                                $successCount++;
                            } else {
                                $failedCount++;
                                $errors[] = "Package {$packageId} invalid transition";
                            }
                            break;
                            
                        case 'assign_driver':
                            if ($currentState->canBeAssigned()) {
                                $package->assign($validated['value']);
                                $successCount++;
                            } else {
                                $failedCount++;
                                $errors[] = "Package {$packageId} cannot be assigned";
                            }
                            break;
                    }
                } catch (\Exception $e) {
                    $failedCount++;
                    $errors[] = "Error with {$packageId}: " . $e->getMessage();
                }
            }

            // Log bulk operation
            DB::table('admin_audit_log')->insert([
                'admin_id' => Auth::id(),
                'action' => 'bulk_' . $validated['action'],
                'target_type' => 'packages',
                'target_id' => json_encode($validated['package_ids']),
                'old_values' => json_encode(['total' => count($validated['package_ids'])]),
                'new_values' => json_encode([
                    'success' => $successCount,
                    'failed' => $failedCount,
                    'errors' => $errors
                ]),
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'created_at' => now()
            ]);

            DB::commit();

            $message = "Bulk operation: {$successCount} successful, {$failedCount} failed.";
            if (!empty($errors)) {
                $message .= " " . implode('; ', array_slice($errors, 0, 3));
            }

            return back()->with($failedCount > 0 ? 'warning' : 'success', $message);

        } catch (\Exception $e) {
            DB::rollback();
            
            Log::error('Bulk operation error', [
                'action' => $validated['action'],
                'admin_id' => Auth::id(),
                'error' => $e->getMessage()
            ]);

            return back()->with('error', 'Bulk operation failed: ' . $e->getMessage());
        }
    }

    /**
     * Export packages data (Web Service Provider)
     */
    public function exportPackagesData(Request $request)
    {
        try {
            // Validate API key
            $apiKey = $request->header('X-Internal-API-Key');
            if ($apiKey !== config('app.internal_api_key')) {
                return response()->json(['error' => 'Unauthorized'], 401);
            }

            $validated = $request->validate([
                'format' => 'nullable|in:json,csv,xml',
                'status' => 'nullable|string',
                'date_from' => 'nullable|date',
                'date_to' => 'nullable|date'
            ]);

            $query = Package::with(['user']);

            if (!empty($validated['status'])) {
                $query->where('package_status', $validated['status']);
            }

            if (!empty($validated['date_from'])) {
                $query->whereDate('created_at', '>=', $validated['date_from']);
            }

            if (!empty($validated['date_to'])) {
                $query->whereDate('created_at', '<=', $validated['date_to']);
            }

            $packages = $query->get();

            return response()->json([
                'success' => true,
                'data' => $packages->map(function ($package) {
                    return [
                        'package_id' => $package->package_id,
                        'tracking_number' => $package->tracking_number,
                        'status' => $package->package_status,
                        'customer_id' => $package->user_id,
                        'customer_email' => $package->user->email ?? null,
                        'weight' => $package->package_weight,
                        'cost' => $package->shipping_cost,
                        'created_at' => $package->created_at->toIso8601String(),
                        'estimated_delivery' => $package->estimated_delivery?->toIso8601String(),
                        'actual_delivery' => $package->actual_delivery?->toIso8601String()
                    ];
                }),
                'metadata' => [
                    'total_count' => $packages->count(),
                    'generated_at' => now()->toIso8601String(),
                    'requested_by_module' => $request->header('X-Module-Name', 'unknown')
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Package data export error', [
                'error' => $e->getMessage(),
                'requested_by' => $request->header('X-Module-Name', 'unknown')
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Export failed'
            ], 500);
        }
    }

    /**
     * Import customer feedback (Web Service Consumer)
     */
    public function importCustomerFeedback()
    {
        try {
            // Call Feedback module's web service
            $response = Http::withHeaders([
                'X-API-Key' => config('services.modules.feedback.api_key'),
                'X-Module-ID' => 'package_module'
            ])
            ->timeout(10)
            ->get(config('services.modules.feedback.base_url') . '/export-for-packages');

            if (!$response->successful()) {
                throw new \Exception('Failed to fetch feedback data');
            }

            $feedbackData = $response->json();

            if (!$feedbackData['success']) {
                throw new \Exception('Invalid feedback data');
            }

            // Process feedback data
            foreach ($feedbackData['data'] as $feedback) {
                if (!empty($feedback['package_id'])) {
                    $this->apiPackageService->update($feedback['package_id'], [
                        'is_rated' => true,
                        'customer_rating' => $feedback['rating'] ?? null,
                        'customer_feedback' => $feedback['comments'] ?? null
                    ]);
                }
            }

            Cache::tags(['packages', 'feedback'])->flush();

            return response()->json([
                'success' => true,
                'message' => 'Feedback imported successfully',
                'processed' => count($feedbackData['data'])
            ]);

        } catch (\Exception $e) {
            Log::error('Feedback import error', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Failed to import feedback'
            ], 500);
        }
    }

    /**
     * Get package statistics
     */
    private function getPackageStatistics(): array
    {
        return Cache::remember('admin_package_stats', 300, function () {
            return [
                'total' => Package::count(),
                'pending' => Package::where('package_status', Package::STATUS_PENDING)->count(),
                'processing' => Package::where('package_status', Package::STATUS_PROCESSING)->count(),
                'in_transit' => Package::where('package_status', Package::STATUS_IN_TRANSIT)->count(),
                'delivered' => Package::where('package_status', Package::STATUS_DELIVERED)->count(),
                'cancelled' => Package::where('package_status', Package::STATUS_CANCELLED)->count(),
                'failed' => Package::where('package_status', Package::STATUS_FAILED)->count(),
                'returned' => Package::where('package_status', Package::STATUS_RETURNED)->count(),
                'revenue_today' => Package::whereDate('created_at', today())
                                         ->where('package_status', '!=', Package::STATUS_CANCELLED)
                                         ->sum('shipping_cost'),
                'deliveries_today' => Package::whereDate('actual_delivery', today())->count()
            ];
        });
    }
}