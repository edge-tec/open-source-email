<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use PragmaRX\Google2FALaravel\Google2FA;

class TwoFactorController extends Controller
{
    public function show()
    {
        if (!auth()->user()->twoFactorAuth?->is_enabled) {
            return redirect('/');
        }

        return view('auth.two-factor');
    }

    public function verify(Request $request)
    {
        $request->validate(['code' => 'required|string|size:6']);

        $user = auth()->user();
        $tfa = $user->twoFactorAuth;

        if (!$tfa || !$tfa->is_enabled) {
            return redirect('/');
        }

        $google2fa = app(Google2FA::class);

        if ($google2fa->verifyKey($tfa->secret, $request->code)) {
            session(['2fa_verified' => true]);
            $tfa->update(['last_used_at' => now()]);

            if ($user->isAdmin()) {
                return redirect()->route('admin.dashboard');
            }
            return redirect()->route('webmail.inbox');
        }

        // Check recovery codes
        $recoveryCodes = $tfa->recovery_codes ?? [];
        if (in_array($request->code, $recoveryCodes)) {
            session(['2fa_verified' => true]);
            $tfa->update([
                'last_used_at' => now(),
                'recovery_codes' => array_values(array_diff($recoveryCodes, [$request->code])),
            ]);

            if ($user->isAdmin()) {
                return redirect()->route('admin.dashboard');
            }
            return redirect()->route('webmail.inbox');
        }

        return back()->withErrors(['code' => 'Invalid verification code.']);
    }
}
