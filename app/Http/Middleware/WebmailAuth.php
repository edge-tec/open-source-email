<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class WebmailAuth
{
    public function handle(Request $request, Closure $next): Response
    {
        // Allow if webmail auth is true and we have the IMAP credentials
        if (session('webmail_auth') === true && session()->has('webmail_password') && session()->has('active_mailbox_id')) {
            return $next($request);
        }

        return redirect()->route('webmail.login');
    }
}
