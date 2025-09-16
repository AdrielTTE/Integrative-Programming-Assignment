<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\Api\FeedbackService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class FeedbackController extends Controller
{
    protected $feedbackService;

    public function __construct(FeedbackService $feedbackService)
    {
        $this->feedbackService = $feedbackService;
    }

    public function getAll()
    {
        return response()->json($this->feedbackService->getAll());
    }

    public function add(Request $request)
    {
        $driver = $this->feedbackService->create($request->all());
        return response()->json($driver, Response::HTTP_CREATED);
    }

    public function get($feedback_id)
    {
        return response()->json($this->feedbackService->getById($feedback_id));
    }

    public function getBatch(Request $request)
{
    $page     = (int) $request->input('page', 1);
    $pageSize = (int) $request->input('pageSize', 10);
    $rating   = $request->input('rating', null);

    // Normalize: if rating is "null" or "all", treat it as no filter
    if ($rating === 'null' || $rating === 'all') {
        $rating = null;
    } elseif (!is_null($rating)) {
        $rating = (int) $rating;
    }

    return $this->feedbackService->getBatch($page, $pageSize, $rating);
}



    public function update(Request $request, $feedback_id)
    {
        $driver = $this->feedbackService->update($feedback_id, $request->all());
        return response()->json($driver);
    }

    public function delete($feedback_id)
    {
        $this->feedbackService->delete($feedback_id);
        return response()->json(null, Response::HTTP_NO_CONTENT);
    }

    public function getCountByStatus(int $rating)
{
    $count = $this->feedbackService->getCountByRating($rating);
    return response()->json(['count' => $count]);
}

}
