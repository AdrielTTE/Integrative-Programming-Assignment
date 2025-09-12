<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Services\ProofService;
use Illuminate\Http\Request;

class ProofController extends Controller
{
    public function __construct(protected ProofService $proofService)
    {
    }

    public function report(Request $request, string $proofId)
    {
        $request->validate([
            'reason' => 'required|string|min:10|max:1000',
        ]);

        try {
            $this->proofService->saveCustomerReport($proofId, $request->input('reason'));

            return back()->with('success', 'Your report has been submitted for review. Thank you.');
        } catch (\Exception $e) {
            return back()->with('error', 'There was an issue submitting your report.');
        }
    }
}