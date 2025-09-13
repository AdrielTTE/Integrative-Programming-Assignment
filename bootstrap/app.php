<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;


return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: base_path('routes/api.php'),
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->api(prepend: [
            \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class,
        ]);

        $middleware->alias([
            'verified' => \App\Http\Middleware\EnsureEmailIsVerified::class,
            'admin'    => \App\Http\Middleware\AdminCheckMiddleware::class,
            'customer' => \App\Http\Middleware\CustomerCheckMiddleware::class,
        ]);

        //
    })
    ->withExceptions(function ($exceptions) {
    $exceptions->render(function (\Illuminate\Auth\AuthenticationException $e, $request) {

        return redirect('/');
    });
})->create();


