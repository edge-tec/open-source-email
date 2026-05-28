<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class WebmailAuth
{
    public function handle(Request $request, Closure $next): Response
    {
        // Allow if user is authenticated admin OR webmail auth is true
        if (auth()->check() || session('webmail_auth') === true) {
            
            // Check if 2FA is required for Admin (if logging in via admin account)
            if (auth()->check()) {
                $user = auth()->user();
                if ($user->twoFactorAuth && $user->twoFactorAuth->is_enabled) {
                    if (!session('2fa_passed')) {
                        return redirect()->route('two-factor.verify');
                    }
                }
            }
            
            return $next($request);
        }

        return redirect()->route('webmail.login');
    }
}
