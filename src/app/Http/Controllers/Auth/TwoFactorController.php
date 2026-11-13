<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Inertia\Response;
use PragmaRX\Google2FA\Google2FA;

/**
 * Two-Factor Authentication Controller (TOTP)
 *
 * Plan reference: Feature 1.5 — MFA + 2FA (TOTP)
 * Tests: T1.5-A, T1.5-B, T1.5-C
 * Constitution principle: HEALTHCARE-GRADE
 */
class TwoFactorController extends Controller
{
    public function __construct(private Google2FA $google2fa) {}

    /**
     * Show the 2FA challenge page.
     * Plan T1.5-A: After login → redirect to /two-factor-challenge if 2FA enabled.
     */
    public function showChallenge(): Response
    {
        return inertia('Auth/TwoFactorChallenge');
    }

    /**
     * Verify TOTP code.
     * Plan T1.5-B: Valid code grants access.
     */
    public function verifyChallenge(Request $request)
    {
        $user = $request->user();

        $request->validate([
            'code' => 'required|string',
        ]);

        $code = str_replace(' ', '', $request->code);

        // Check TOTP
        $valid = $this->google2fa->verifyKey(
            decrypt($user->two_factor_secret),
            $code
        );

        // Check backup codes if TOTP failed
        if (! $valid) {
            $valid = $this->checkBackupCode($user, $code);
        }

        if (! $valid) {
            return back()->withErrors(['code' => __('The provided two-factor code was invalid.')]);
        }

        // Mark session as 2FA-verified
        $request->session()->put('two_factor_verified', true);

        return redirect()->intended(route('dashboard'));
    }

    /**
     * Show the 2FA setup page (enable TOTP).
     */
    public function showSetup()
    {
        $user   = auth()->user();
        $secret = $this->google2fa->generateSecretKey();

        $qrCodeUrl = $this->google2fa->getQRCodeUrl(
            config('app.name'),
            $user->email,
            $secret
        );

        // Store secret temporarily until confirmed
        $user->update(['two_factor_secret' => encrypt($secret)]);

        return inertia('Auth/TwoFactorSetup', [
            'qr_code_url' => $qrCodeUrl,
            'secret'      => $secret,
        ]);
    }

    /**
     * Confirm and activate 2FA.
     * Plan T1.5-A: 2FA required after enabling (once confirmed_at is set).
     */
    public function confirmSetup(Request $request)
    {
        $request->validate(['code' => 'required|string|digits:6']);

        $user = $request->user();
        $valid = $this->google2fa->verifyKey(
            decrypt($user->two_factor_secret),
            $request->code
        );

        if (! $valid) {
            return back()->withErrors(['code' => 'Invalid verification code.']);
        }

        $backupCodes = $this->generateBackupCodes();

        $user->update([
            'two_factor_enabled'      => true,
            'two_factor_confirmed_at' => now(),
            'two_factor_backup_codes' => array_map(fn ($c) => Hash::make($c), $backupCodes),
        ]);

        return back()->with([
            'success'      => '2FA enabled successfully.',
            'backup_codes' => $backupCodes, // shown once — user must save these
        ]);
    }

    /**
     * Disable 2FA.
     */
    public function disable(Request $request)
    {
        $request->validate(['password' => 'required|current_password']);

        $request->user()->update([
            'two_factor_enabled'      => false,
            'two_factor_secret'       => null,
            'two_factor_backup_codes' => null,
            'two_factor_confirmed_at' => null,
        ]);

        return back()->with('success', '2FA has been disabled.');
    }

    private function checkBackupCode($user, string $code): bool
    {
        $codes = $user->two_factor_backup_codes ?? [];
        foreach ($codes as $index => $hashedCode) {
            if (Hash::check($code, $hashedCode)) {
                // Invalidate used backup code
                unset($codes[$index]);
                $user->update(['two_factor_backup_codes' => array_values($codes)]);
                return true;
            }
        }
        return false;
    }

    private function generateBackupCodes(int $count = 8): array
    {
        return array_map(
            fn () => strtoupper(Str::random(4)) . '-' . strtoupper(Str::random(4)),
            range(1, $count)
        );
    }
}
