<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Repositories\PackageRepository;
use App\Services\PackageService;

class PackageServiceProvider extends ServiceProvider
{
    public function register()
    {
        // Bind Repository
        $this->app->bind(PackageRepository::class, function ($app) {
            return new PackageRepository();
        });

        // Bind Service
        $this->app->bind(PackageService::class, function ($app) {
            return new PackageService($app->make(PackageRepository::class));
        });
    }

    public function boot()
    {
        //
    }
}