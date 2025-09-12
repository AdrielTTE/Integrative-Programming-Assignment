<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\Strategies\Proof\VerificationStrategyInterface;
use App\Services\Strategies\Proof\BasicVerificationStrategy;
use App\Services\ProofService;

class ProofServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Bind the interface to its concrete implementation.
        $this->app->bind(
            VerificationStrategyInterface::class,
            BasicVerificationStrategy::class
        );

        // Register ProofService as a singleton with its dependencies resolved.
        $this->app->singleton(ProofService::class, function ($app) {
            return new ProofService(
                $app->make(VerificationStrategyInterface::class)
            );
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // No boot logic needed at this time.
    }
}