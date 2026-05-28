<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class TwoFactorMiddleware
{
    /**
     * Check if user needs to complete 2FA verification.
     */
    public function handle(Request $request, Closure $next)
    {
        $user = auth()->user();

        if (!$user) {
            return $next($request);
        }

        // Check if 2FA is enabled for this user
        if ($user->twoFactorAuth && $user->twoFactorAuth->is_enabled) {
            // Check if already verified in session
            if (!session('2fa_verified')) {
                return redirect()->route('two-factor.verify');
            }
        }

        return $next($request);
    }
}
