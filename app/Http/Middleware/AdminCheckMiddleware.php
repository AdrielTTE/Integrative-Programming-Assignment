<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class AdminCheckMiddleware
{
    public function handle($request, Closure $next)
    {
        if (Auth::check() && str_starts_with(Auth::user()->user_id, 'AD')) {
            return $next($request);
        }

        Auth::logout();
        return redirect()->route('admin.login')->withErrors(['email' => 'Access denied.']);
    }
}
