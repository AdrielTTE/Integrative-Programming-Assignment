<?php

namespace App\Http\Controllers\CustomerControllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\CreatePackageRequest;
use App\Http\Requests\UpdatePackageRequest;
use App\Services\PackageService;
use App\Models\Package;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Exception;

class PackageController extends Controller
{
    private PackageService $packageService;
    private PaymentFacade $paymentFacade;

    public function __construct(PackageService $packageService, PaymentFacade $paymentFacade)
    {
        $this->packageService = $packageService;
        $this->paymentFacade = $paymentFacade;
    }

    public function index(Request $request)
    {
        $packages = Package::where('user_id', Auth::id())->get();
        
        // CONSUME Payment Module - get payment status for each package
        $packagesWithPayment = $packages->map(function($package) {
            $paymentStatus = $this->paymentFacade->getPackagePaymentStatus($package->package_id);
            $package->payment_details = $paymentStatus;
            return $package;
        });

        return view('customer.packages.index', compact('packagesWithPayment'));
    }

    public function create()
    {
        $priorities = Package::getPriorities();
        return view('customer.packages.create', compact('priorities'));
    }

    public function store(CreatePackageRequest $request)
{
    try {
        // Create package without payment
        $package = $this->packageService->createPackage($request->validated());

        // Redirect to payment page instead of showing success
        return redirect()
            ->route('customer.payment.make', $package->package_id)
            ->with('success', 'Package created successfully! Please complete payment to process your delivery request.')
            ->with('package_created', true);

    } catch (Exception $e) {
        return back()
            ->withInput()
            ->with('error', 'Failed to create delivery request: ' . $e->getMessage());
    }
}

    public function show(string $packageId)
    {
        $package = Package::where('package_id', $packageId)->firstOrFail();
        
        // CONSUME Payment Module - get payment status
        $paymentStatus = $this->paymentFacade->getPackagePaymentStatus($packageId);
        
        // CONSUME Payment Module - check refund availability
        $canRefund = $this->paymentFacade->isRefundAvailable($packageId);
        
        $history = $this->packageService->getPackageHistory($packageId);
        $currentState = $package->getState();

        return view('customer.packages.show', compact(
            'package', 'history', 'currentState', 'paymentStatus', 'canRefund'
        ));
    }

    public function edit(string $packageId)
    {
        $package = Package::where('package_id', $packageId)
                         ->where('user_id', Auth::id())
                         ->firstOrFail();

        if (!$package->canBeEdited()) {
            return redirect()
                ->route('customer.packages.show', $packageId)
                ->with('error', 'This package cannot be modified in its current status.');
        }

        $priorities = Package::getPriorities();
        return view('customer.packages.edit', compact('package', 'priorities'));
    }

    public function update(UpdatePackageRequest $request, string $packageId)
{
    try {
        $package = Package::where('package_id', $packageId)
                         ->where('user_id', Auth::id())
                         ->firstOrFail();

        // Update only the allowed fields, preserving weight and shipping_cost
        $package->update([
            'package_contents' => $request->package_contents,
            'package_dimensions' => $request->package_dimensions,
            'sender_address' => $request->sender_address,
            'recipient_address' => $request->recipient_address,
            'notes' => $request->notes,
        ]);

        return redirect()
            ->route('customer.packages.show', $packageId)
            ->with('success', 'Package details updated successfully!');

    } catch (Exception $e) {
        return back()
            ->withInput()
            ->with('error', 'Failed to update package: ' . $e->getMessage());
    }
}

    public function destroy(string $packageId)
    {
        $package = Package::where('package_id', $packageId)->firstOrFail();
        
        // CONSUME Payment Module - check if refund is needed
        $paymentStatus = $this->paymentFacade->getPackagePaymentStatus($packageId);
        
        if ($paymentStatus['has_payment'] && $paymentStatus['can_refund']) {
            // CONSUME Payment Module - request refund
            $refundResult = $this->paymentFacade->requestRefund(
                $paymentStatus['payment_id'], 
                'Package cancelled by customer'
            );
            
            if ($refundResult['success']) {
                $message = 'Package cancelled and refund requested successfully.';
            } else {
                $message = 'Package cancelled. Refund request failed: ' . $refundResult['message'];
            }
        } else {
            $message = 'Package cancelled successfully.';
        }

        $this->packageService->cancelPackage($packageId, Auth::user());
        
        return redirect()->route('customer.packages.index')->with('success', $message);
    }

    public function process(string $packageId)
    {
        try {
            $this->packageService->processPackage($packageId);

            return back()->with('success', 'Package processed successfully!');

        } catch (Exception $e) {
            return back()->with('error', 'Failed to process package: ' . $e->getMessage());
        }
    }
    public function showPayment(string $packageId)
{
    try {
        $package = Package::where('package_id', $packageId)
                         ->where('user_id', Auth::id())
                         ->firstOrFail();

        // Check if payment is required
        if ($package->payment_status === 'paid') {
            return redirect()->route('customer.packages.show', $packageId)
                           ->with('info', 'This package has already been paid for.');
        }

        // Check if package can still be paid for
        if (in_array($package->package_status, ['cancelled', 'delivered'])) {
            return redirect()->route('customer.packages.show', $packageId)
                           ->with('error', 'Payment is not available for this package status.');
        }

        return redirect()->route('customer.payment.make', $packageId);
        
    } catch (\Exception $e) {
        return redirect()->route('customer.packages.index')
                       ->with('error', 'Package not found or access denied.');
    }
}
}
