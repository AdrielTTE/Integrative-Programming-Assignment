<?php
namespace App\Http\Controllers\CustomerControllers;
use App\Models\Delivery;
use App\Http\Controllers\Controller;
use App\Services\Api\DeliveryService;
use Illuminate\Notifications\Notification;
use App\Services\Api\PackageService;
use App\Services\CustomerServices\FeedbackService;
use Illuminate\Http\Request;
use App\Models\Feedback;

class FeedbackController extends Controller{

protected PackageService $packageService;
protected DeliveryService $deliveryService;
protected FeedbackService $feedbackService;

public function __construct()
{
    $this->feedbackService = new FeedbackService();
}
    public function feedback(Request $request){

        $page = (int) $request->input('page', 1);     // Default to page 1
        $userId = auth()->user()->user_id;

        $packages = $this->feedbackService->getDeliveredPackages('DELIVERED',$page, 10, $userId);

        return view('CustomerViews.feedback', compact('packages'));
    }

 public function store(Request $request)
{
    //$delivery = $this->feedbackService->getDeliveryByPackageID($request->package_id);

    //$request->merge([
    //    'delivery_id' => $delivery->delivery_id,
    //]);

    $validated = $request->validate([
        'package_id' => 'required|string|exists:package,package_id',
        'rating'      => 'required|integer|min:1|max:5',
        'category'    => 'required|string|max:50',
        'comment'     => 'nullable|string',
    ]);

    $customerId = auth()->user()->user_id;

    // Check if feedback already exists for this delivery & customer
    $feedback = Feedback::where('package_id', $validated['package_id'])
                        ->where('customer_id', $customerId)
                        ->first();

    if ($feedback) {
    $feedback->update([
        'rating'    => $validated['rating'],
        'category'  => $validated['category'],
        'comment'   => $validated['comment'] ?? null,
    ]);

} else {
    $latestId = Feedback::max('feedback_id');

    if ($latestId) {
        $num = (int) substr($latestId, 1);
        $newId = 'F' . str_pad($num + 1, 5, '0', STR_PAD_LEFT);
    } else {
        $newId = 'F00001';
    }

    $createdFeedback = Feedback::create([
        'feedback_id' => $newId,
        'package_id'  => $validated['package_id'],
        'customer_id' => $customerId,
        'rating'      => $validated['rating'],
        'category'    => $validated['category'],
        'comment'     => $validated['comment'] ?? null,
    ]);

    $this->feedbackService->updatePackageFeedback($request->package_id);


}


        return redirect()->back()->with('success', 'Thank you for your feedback!');
    }
}









