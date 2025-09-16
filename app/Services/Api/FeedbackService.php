<?php

namespace App\Services\Api;

use App\Models\Feedback;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class FeedbackService
{
    public function getAll()
    {
        return Feedback::all();
    }

    public function create(array $data)
    {
        $validator = Validator::make($data, [
            'feedback_id'  => 'required|string|unique:feedback,feedback_id|max:10',
            'delivery_id'  => 'required|string|exists:delivery,delivery_id|max:10',
            'customer_id'  => 'required|string|exists:customer,customer_id|max:10',
            'rating'       => 'required|integer|min:1|max:5',
            'comment'      => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        return Feedback::create($validator->validated());
    }

    public function getById(string $id)
    {
        return Feedback::findOrFail($id);
    }

    public function getBatch(int $pageNo, int $perPage, ?int $rating = null)
{
    $query = Feedback::query();

    // Only filter if rating was provided
    if (!is_null($rating)) {
        $query->where('rating', $rating);
    }

    return $query->paginate($perPage, ['*'], 'page', $pageNo);
}


    public function update(string $id, array $data)
    {
        $feedback = Feedback::findOrFail($id);

        $validator = Validator::make($data, [
            'delivery_id'  => 'required|string|exists:delivery,delivery_id|max:10',
            'customer_id'  => 'required|string|exists:customer,customer_id|max:10',
            'rating'       => 'required|integer|min:1|max:5',
            'comment'      => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        $feedback->update($validator->validated());
        return $feedback;
    }

    public function delete(string $id): void
    {
        $feedback = Feedback::findOrFail($id);
        $feedback->delete();
    }

    public function getCountByRating(int $rating): int
    {
        return Feedback::where('rating', $rating)->count();
    }
}
