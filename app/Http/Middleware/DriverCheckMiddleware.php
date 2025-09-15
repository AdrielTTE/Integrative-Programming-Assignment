<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;
class DriverCheckMiddleware{
    public function handle($request, Closure $next)
{
    if (Auth::check() && str_starts_with(Auth::user()->user_id, 'D')) {
        return $next($request);
    }

    Auth::logout();
    return redirect()->route('driver.login')->withErrors(['email' => 'Access denied.']);
}


}
