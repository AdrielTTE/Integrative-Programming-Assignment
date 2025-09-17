<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Repositories\PackageRepository;
use App\Services\PackageService;
use App\Services\Api\PackageService as ApiPackageService;
use App\Factories\PackageStateFactory;

class PackageServiceProvider extends ServiceProvider
{
    public function register()
    {
        // Bind Repository
        $this->app->bind(PackageRepository::class, function ($app) {
            return new PackageRepository();
        });

        // Bind Main Service
        $this->app->bind(PackageService::class, function ($app) {
            return new PackageService($app->make(PackageRepository::class));
        });

        // Bind API Service
        $this->app->bind(ApiPackageService::class, function ($app) {
            return new ApiPackageService();
        });

        // Bind State Factory (Singleton for efficiency)
        $this->app->singleton(PackageStateFactory::class, function ($app) {
            return new PackageStateFactory();
        });
    }

    public function boot()
    {
        //
    }
}