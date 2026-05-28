<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class InstallerMiddleware
{
    /**
     * Block access to installer if app is already installed.
     */
    public function handle(Request $request, Closure $next)
    {
        if (config('edgemail.installed') || env('APP_INSTALLED', false)) {
            return redirect('/');
        }

        return $next($request);
    }
}
