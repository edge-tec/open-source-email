<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class InstalledMiddleware
{
    /**
     * Redirect to installer if app is not yet installed.
     * Skip for installer routes and API routes.
     */
    public function handle(Request $request, Closure $next)
    {
        // Skip for installer routes
        if ($request->is('install*')) {
            return $next($request);
        }

        // Skip for API routes
        if ($request->is('api/*')) {
            return $next($request);
        }

        // Check if installed
        if (!config('edgemail.installed') && !env('APP_INSTALLED', false)) {
            return redirect()->route('installer.welcome');
        }

        return $next($request);
    }
}
