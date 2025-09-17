<?php



namespace App\Http\Controllers\CustomerControllers;

use App\Http\Controllers\Controller;
use App\Services\AdminServices\FeedbackApiFacade;
use Illuminate\Http\Request;

class TemporaryController extends Controller{
protected FeedbackApiFacade $feedbackService;

public function __construct(FeedbackApiFacade $feedbackService)
{
    $this->feedbackService = $feedbackService;
}

    public function temporaryPage()
{
    return view('CustomerViews.temporaryPage');
}

}
