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
    $request->validate([
        'delivery_id' => 'required|string',
        'customer_id' => 'required|string',
        'rating' => 'required|integer|min:1|max:5',
        'category' => 'required|string|max:50',
        'comment' => 'nullable|string',
    ]);

    Feedback::create($request->all());

    return redirect()->back()->with('success', 'Thank you for your feedback!');
}


}
