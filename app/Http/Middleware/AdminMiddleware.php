<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class AdminMiddleware
{
    /**
     * Restrict access to admin and super_admin roles.
     */
    public function handle(Request $request, Closure $next)
    {
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        $user = auth()->user();
        if (!in_array($user->role, ['super_admin', 'admin', 'reseller'])) {
            abort(403, 'Unauthorized access.');
        }

        return $next($request);
    }
}
