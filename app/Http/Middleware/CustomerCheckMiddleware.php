<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;
class CustomerCheckMiddleware{
    public function handle($request, Closure $next)
{
    if (Auth::check() && str_starts_with(Auth::user()->user_id, 'C')) {
        return $next($request);
    }

    Auth::logout();
    return redirect()->route('customer.login')->withErrors(['email' => 'Access denied.']);
}


}
