<?php



namespace App\Http\Controllers\AdminControllers;

use App\Http\Controllers\Controller;
use App\Services\AdminServices\FeedbackAndRatingService;
use Illuminate\Http\Request;

class FeedbackController extends Controller{
protected FeedbackAndRatingService $feedbackService;

public function __construct()
{
    $this->feedbackService = new FeedbackAndRatingService();
}

    public function feedback(Request $request)
{
    $page     = (int) $request->input('page', 1);
    $pageSize = (int) $request->input('pageSize', 10);
    $rating   = $request->input('rating', null); // default = all

    $feedbacks = $this->feedbackService->getBatch($page, $pageSize, $rating);

    return view('AdminViews.feedback', compact('feedbacks'));
}

}
