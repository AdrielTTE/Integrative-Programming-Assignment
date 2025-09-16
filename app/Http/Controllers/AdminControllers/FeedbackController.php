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

    public function feedbac(Request $request){

        return view('AdminViews.FeedbackAndRating.dashboard', );
    }
}
