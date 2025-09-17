<?php

namespace App\Services\AdminServices;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class FeedbackApiFacade
{
    protected string $baseUrl;

    public function __construct()
    {
        // You can make this configurable via .env
        $this->baseUrl = config('services.api.base_url', 'http://localhost:8001/api');
    }

      public function getBatch(int $page, int $pageSize, $rating = null, string $category)
{
    $response = Http::get("{$this->baseUrl}/feedback/getBatch", [
        'page'     => $page,
        'pageSize' => $pageSize,
        'rating'   => $rating,
        'category'=> $category,
    ]);

    $data = $response->json();
    $items = $data['data'] ?? [];

    return new LengthAwarePaginator(
        $items,
        $data['total'] ?? count($items),
        $pageSize,
        $page,
        ['path' => request()->url(), 'query' => request()->query()]
    );
}






}
