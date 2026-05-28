<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\FailedLogin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        // Check if IP is banned
        $banned = FailedLogin::where('ip_address', $request->ip())
            ->where('is_banned', true)
            ->where('banned_until', '>', now())
            ->exists();

        if ($banned) {
            return back()->withErrors(['email' => 'Your IP has been temporarily banned due to too many failed login attempts.']);
        }

        if (Auth::attempt($request->only('email', 'password'), $request->boolean('remember'))) {
            $request->session()->regenerate();
            $request->session()->put('webmail_password', encrypt($request->password));

            $user = Auth::user();
            $user->update([
                'last_login_at' => now(),
                'last_login_ip' => $request->ip(),
            ]);

            // Check if user needs 2FA
            if ($user->twoFactorAuth && $user->twoFactorAuth->is_enabled) {
                return redirect()->route('two-factor.verify');
            }

            // Redirect based on role
            if ($user->isAdmin()) {
                return redirect()->route('admin.dashboard');
            }

            return redirect()->route('webmail.inbox');
        }

        // Record failed login
        FailedLogin::create([
            'ip_address' => $request->ip(),
            'email' => $request->email,
            'user_agent' => $request->userAgent(),
            'service' => 'web',
        ]);

        // Check if should ban
        $maxAttempts = (int) config('edgemail.max_login_attempts', 5);
        $recentFails = FailedLogin::where('ip_address', $request->ip())
            ->where('created_at', '>', now()->subHour())
            ->count();

        if ($recentFails >= $maxAttempts) {
            FailedLogin::create([
                'ip_address' => $request->ip(),
                'email' => $request->email,
                'service' => 'web',
                'is_banned' => true,
                'banned_until' => now()->addSeconds((int) config('edgemail.ban_duration', 3600)),
            ]);
        }

        return back()->withErrors(['email' => 'Invalid email or password.'])->withInput($request->only('email'));
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login');
    }
}
