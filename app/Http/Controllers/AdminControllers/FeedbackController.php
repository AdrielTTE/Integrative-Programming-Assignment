<?php



namespace App\Http\Controllers\AdminControllers;

use App\Http\Controllers\Controller;
use App\Services\AdminServices\FeedbackApiFacade;
use Illuminate\Http\Request;

class FeedbackController extends Controller{
protected FeedbackApiFacade $feedbackService;

public function __construct(FeedbackApiFacade $feedbackService)
{
    $this->feedbackService = $feedbackService;
}

    public function feedback(Request $request)
{
    $page     = (int) $request->input('page', 1);
    $pageSize = (int) $request->input('pageSize', 10);
    $rating   = $request->input('rating', null); // default = all
    $category = (string)$request->input('category', 'all');

    $feedbacks = $this->feedbackService->getBatch($page, $pageSize, $rating, $category);

    return view('AdminViews.feedback', compact('feedbacks'));
}

}
