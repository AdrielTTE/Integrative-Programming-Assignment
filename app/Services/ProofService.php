<?php

namespace App\Services;

use App\Models\Delivery;
use App\Models\ProofOfDelivery;
use App\Services\Strategies\Proof\VerificationStrategyInterface;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;

class ProofService
{
    protected VerificationStrategyInterface $verificationStrategy;
    protected string $baseUrl;

    public function __construct(VerificationStrategyInterface $verificationStrategy)
    {
        $this->verificationStrategy = $verificationStrategy;
        $this->baseUrl = config('services.api.base_url', 'http://localhost:8001/api');
    }

    protected function createPaginator(array $response): LengthAwarePaginator
    {
        $items = $response['data'] ?? [];
        $total = $response['total'] ?? 0;
        $perPage = $response['per_page'] ?? 15;
        $currentPage = $response['current_page'] ?? 1;

        $hydratedItems = collect($items)->map(function ($item) {
            // Manually create model instances to ensure compatibility with Blade templates
            $proof = new ProofOfDelivery((array)$item);
            if (!empty($item['delivery'])) {
                $proof->setRelation('delivery', new \App\Models\Delivery((array)$item['delivery']));
                if (!empty($item['delivery']['package'])) {
                    $proof->delivery->setRelation('package', new \App\Models\Package((array)$item['delivery']['package']));
                }
            }
            if (!empty($item['verifier'])) {
                $proof->setRelation('verifier', new \App\Models\User((array)$item['verifier']));
            }
            return $proof;
        });

        return new LengthAwarePaginator($hydratedItems, $total, $perPage, $currentPage, [
            'path' => request()->url(),
            'query' => request()->query(),
        ]);
    }

    public function getProofForAdmin(string $proofId): ProofOfDelivery
    {
        $response = Http::get("{$this->baseUrl}/proofOfDelivery/{$proofId}")->throw()->json();
        $proof = new ProofOfDelivery($response);
        // Manually hydrate relationships if needed by the view
        if (!empty($response['delivery'])) {
            $delivery = new \App\Models\Delivery((array)$response['delivery']);
            if(!empty($response['delivery']['package'])){
                 $delivery->setRelation('package', new \App\Models\Package((array)$response['delivery']['package']));
                 if(!empty($response['delivery']['package']['customer'])){
                     $delivery->package->setRelation('customer', new \App\Models\Customer((array)$response['delivery']['package']['customer']));
                 }
            }
            $proof->setRelation('delivery', $delivery);
        }
        if (!empty($response['verifier'])) {
             $proof->setRelation('verifier', new \App\Models\User((array)$response['verifier']));
        }
        return $proof;
    }

    public function verifyProof(ProofOfDelivery $proof): array
    {
        return $this->verificationStrategy->verify($proof);
    }

    public function getProofsAwaitingVerification(): LengthAwarePaginator
    {
        $response = Http::get("{$this->baseUrl}/proofOfDelivery", [
            'status' => 'awaiting_verification',
            'page' => request('page', 1)
        ])->throw()->json();

        return $this->createPaginator($response);
    }

    public function getAllProofsPaginated(): LengthAwarePaginator
    {
        $response = Http::get("{$this->baseUrl}/proofOfDelivery/history", [
            'page' => request('page', 1)
        ])->throw()->json();
        return $this->createPaginator($response);
    }

    public function processVerification(string $proofId, string $action, ?string $reason = null): string
    {
        $response = Http::post("{$this->baseUrl}/proofOfDelivery/{$proofId}/process", [
            'action' => $action,
            'reason' => $reason,
            'admin_id' => Auth::id(), // Pass the admin ID to the API
        ])->throw()->json();

        return $response['message'] ?? 'Action processed successfully.';
    }

    public function getProofByPackageId(string $packageId)
    {
        $response = Http::get("{$this->baseUrl}/package/{$packageId}/proof")->throw()->json();
        return new ProofOfDelivery($response);
    }

    public function getProofMetadata(ProofOfDelivery $proof): array
    {
        // This logic can remain as it seems to be generating simulated/static data
        if ($proof->proof_type === 'PHOTO') {
            return [
                'Capture Device' => 'MobileApp v2.1 (Android)',
                'GPS Coordinates' => '3.1390° N, 101.6869° E (Simulated)',
                'Device IP Address' => '101.32.115.45 (Simulated)',
            ];
        }
        return [];
    }

    public function getProofsForCustomer(): LengthAwarePaginator
    {
        $customerId = Auth::id();
        $response = Http::get("{$this->baseUrl}/customer/{$customerId}/proofs", [
             'page' => request('page', 1)
        ])->throw()->json();
        
        return $this->createPaginator($response);
    }
    
    public function saveCustomerReport(string $proofId, string $reason): bool
    {
        $response = Http::post("{$this->baseUrl}/proofOfDelivery/{$proofId}/report", [
            'reason' => $reason,
            'customer_id' => Auth::id(),
        ])->throw();

        return $response->successful();
    }
}