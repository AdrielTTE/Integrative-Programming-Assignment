<?php

namespace App\Services\AdminServices;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Collection;

class FeedbackAndRatingService
{
    protected string $baseUrl;

    public function __construct()
    {
        // You can make this configurable via .env
        $this->baseUrl = config('services.api.base_url', 'http://localhost:8001/api');
    }



}
