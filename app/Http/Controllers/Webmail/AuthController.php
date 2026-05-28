<?php

namespace App\Http\Controllers\Webmail;

use App\Http\Controllers\Controller;
use App\Models\Mailbox;
use App\Models\FailedLogin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function showLoginForm()
    {
        // If already logged in
        if (auth()->check() || session('webmail_auth') === true) {
            return redirect()->route('webmail.inbox');
        }
        return view('webmail.auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        // Rate limiting logic
        $banned = FailedLogin::where('ip_address', $request->ip())
            ->where('is_banned', true)
            ->where('banned_until', '>', now())
            ->exists();

        if ($banned) {
            return back()->withErrors(['email' => 'Your IP has been temporarily banned due to too many failed login attempts.']);
        }

        $mailbox = Mailbox::where('email', $request->email)->active()->first();

        if ($mailbox && Hash::check($request->password, $mailbox->password)) {
            $request->session()->regenerate();
            $request->session()->put('webmail_auth', true);
            $request->session()->put('active_mailbox_id', $mailbox->id);
            $request->session()->put('webmail_password', encrypt($request->password));

            $mailbox->update([
                'last_login_at' => now(),
                'last_login_ip' => $request->ip(),
            ]);

            return redirect()->route('webmail.inbox');
        }

        // Record failed login
        FailedLogin::create([
            'ip_address' => $request->ip(),
            'email' => $request->email,
            'user_agent' => $request->userAgent(),
            'service' => 'webmail',
        ]);

        // Check ban threshold
        $maxAttempts = (int) config('edgemail.max_login_attempts', 5);
        $recentFails = FailedLogin::where('ip_address', $request->ip())
            ->where('created_at', '>', now()->subHour())
            ->count();

        if ($recentFails >= $maxAttempts) {
            FailedLogin::create([
                'ip_address' => $request->ip(),
                'email' => $request->email,
                'service' => 'webmail',
                'is_banned' => true,
                'banned_until' => now()->addSeconds((int) config('edgemail.ban_duration', 3600)),
            ]);
        }

        return back()->withErrors(['email' => 'Invalid email or password.'])->withInput($request->only('email'));
    }

    public function logout(Request $request)
    {
        if (session('webmail_auth')) {
            $request->session()->forget(['webmail_auth', 'active_mailbox_id', 'webmail_password']);
            $request->session()->regenerateToken();
            return redirect()->route('webmail.login');
        }

        // If it's an admin impersonating, clear active_mailbox_id and return to dashboard
        if (auth()->check()) {
            $request->session()->forget(['active_mailbox_id']);
            return redirect()->route('admin.mailboxes.index');
        }

        return redirect()->route('login');
    }
}
