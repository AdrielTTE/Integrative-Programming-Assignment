<?php

namespace App\Services\WebServices;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PackageWebServiceClient
{
    protected string $baseUrl;

    public function __construct()
    {
        $this->baseUrl = config('services.package_module.base_url', 'http://localhost:8001/api/ws');
    }

    /**
     * Consume Package Module Service: Get Package Details
     */
    public function getPackageDetails(string $packageId, int $queryFlag = 3): array
    {
        try {
            $response = Http::timeout(30)
                ->withHeaders([
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json',
                    'X-API-Key' => config('services.package_module.api_key')
                ])
                ->post("{$this->baseUrl}/package/details", [
                    'package_id' => $packageId,
                    'query_flag' => $queryFlag
                ]);

            if ($response->successful()) {
                return $response->json();
            }

            Log::error('Failed to get package details', [
                'package_id' => $packageId,
                'status' => $response->status(),
                'response' => $response->body()
            ]);

            return [
                'status' => 'ERROR',
                'message' => 'Failed to retrieve package details'
            ];

        } catch (\Exception $e) {
            Log::error('Package service request failed', [
                'package_id' => $packageId,
                'error' => $e->getMessage()
            ]);

            return [
                'status' => 'ERROR',
                'message' => 'Service unavailable'
            ];
        }
    }

    /**
     * Notify Package Module of Payment Status
     */
    public function updatePaymentStatus(string $packageId, string $paymentId, string $status, string $method): array
    {
        try {
            $response = Http::timeout(30)
                ->withHeaders([
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json',
                    'X-API-Key' => config('services.package_module.api_key')
                ])
                ->put("{$this->baseUrl}/package/payment-status", [
                    'package_id' => $packageId,
                    'payment_id' => $paymentId,
                    'payment_status' => $status,
                    'payment_method' => $method
                ]);

            if ($response->successful()) {
                return $response->json();
            }

            return [
                'status' => 'ERROR',
                'message' => 'Failed to update payment status'
            ];

        } catch (\Exception $e) {
            Log::error('Payment status update failed', [
                'package_id' => $packageId,
                'error' => $e->getMessage()
            ]);

            return [
                'status' => 'ERROR',
                'message' => 'Service unavailable'
            ];
        }
    }
}