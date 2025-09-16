<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\PackageCommandInvoker;

class CustomerPackageServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton(PackageCommandInvoker::class, function ($app) {
            return new PackageCommandInvoker();
        });
    }

    public function boot()
    {
        //
    }
}