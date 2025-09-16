<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Package;

class CustomerPackageAccessMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        if ($packageId = $request->route('packageId')) {
            $package = Package::where('package_id', $packageId)
                             ->where('customer_id', Auth::id())
                             ->first();

            if (!$package) {
                abort(404, 'Package not found or access denied');
            }

            // Add package to request for controller use
            $request->attributes->set('package', $package);
        }

        return $next($request);
    }
}