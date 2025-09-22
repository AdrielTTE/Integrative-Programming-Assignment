<?php

namespace App\Http\Controllers\WebServices;

use App\Http\Controllers\Controller;
use App\Services\PackageService;
use App\Models\Package;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PackageWebServiceController extends Controller
{
    protected PackageService $packageService;

    public function __construct(PackageService $packageService)
    {
        $this->packageService = $packageService;
    }

    /**
     * WS001: Get Package Details for Payment Processing
     * IFA: Package Module -> Payment Module
     */
    public function getPackageDetailsForPayment(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'package_id' => 'required|string|exists:package,package_id',
            'query_flag' => 'required|integer|in:1,2,3'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'ERROR',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 400);
        }

        try {
            $package = Package::find($request->package_id);
            
            $response = [
                'status' => 'SUCCESS',
                'package_id' => $package->package_id,
                'tracking_number' => $package->tracking_number,
                'package_status' => $package->package_status,
                'payment_required' => $package->payment_status !== 'paid'
            ];

            // Based on query_flag, return different data
            switch ($request->query_flag) {
                case 1: // Basic info only
                    $response['shipping_cost'] = $package->shipping_cost;
                    break;
                case 2: // With customer details
                    $response['customer_details'] = [
                        'user_id' => $package->user_id,
                        'sender_address' => $package->sender_address,
                        'recipient_address' => $package->recipient_address
                    ];
                    break;
                case 3: // Complete details
                    $response['shipping_cost'] = $package->shipping_cost;
                    $response['customer_details'] = [
                        'user_id' => $package->user_id,
                        'sender_address' => $package->sender_address,
                        'recipient_address' => $package->recipient_address
                    ];
                    $response['package_details'] = [
                        'weight' => $package->package_weight,
                        'dimensions' => $package->package_dimensions,
                        'contents' => $package->package_contents,
                        'priority' => $package->priority,
                        'estimated_delivery' => $package->estimated_delivery
                    ];
                    break;
            }

            return response()->json($response);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'ERROR',
                'message' => 'Failed to retrieve package details',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * WS002: Update Package Payment Status
     * IFA: Package Module <- Payment Module
     */
    public function updatePackagePaymentStatus(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'package_id' => 'required|string|exists:package,package_id',
            'payment_id' => 'required|string',
            'payment_status' => 'required|string|in:paid,pending,failed,refunded',
            'payment_method' => 'required|string|in:credit_card,debit_card,online_banking,e_wallet'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'ERROR',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 400);
        }

        try {
            $package = Package::find($request->package_id);
            
            $package->payment_status = $request->payment_status;
            $package->payment_id = $request->payment_id;
            
            // Auto-update package status if payment successful
            if ($request->payment_status === 'paid' && $package->package_status === 'pending') {
                $package->package_status = 'processing';
            }
            
            $package->save();

            return response()->json([
                'status' => 'SUCCESS',
                'package_id' => $package->package_id,
                'payment_status' => $package->payment_status,
                'package_status' => $package->package_status,
                'message' => 'Package payment status updated successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'ERROR',
                'message' => 'Failed to update payment status',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}