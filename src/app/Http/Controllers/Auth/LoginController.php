<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Authentication Controller.
 *
 * Plan reference: Feature 1.4 — IAM, login/logout
 * Spec §3 Business Rules:
 *   Rule 1: Inactive users cannot log in.
 *   Rule 4: Failed login attempts must be logged.
 */
class LoginController extends Controller
{
    public function create(): Response
    {
        return Inertia::render('Auth/Login');
    }

    public function store(LoginRequest $request): RedirectResponse
    {
        $credentials = $request->only('email', 'password');

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $user = Auth::user();

            // Spec §3 Rule 1: Inactive users cannot log in.
            if (! $user->is_active) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return back()->withErrors([
                    'email' => __('auth.deactivated'),
                ])->onlyInput('email');
            }

            $request->session()->regenerate();

            // Rule 4: Log the successful login
            $user->update([
                'last_login_at'       => now(),
                'last_login_ip'       => $request->ip(),
                'failed_login_count'  => 0, // reset on success
            ]);

            return redirect()->intended(route('dashboard'));
        }

        // Rule 4: Track failed attempts (best-effort, field may not exist in older schemas)
        try {
            \App\Models\User::where('email', $request->email)
                ->increment('failed_login_count');
        } catch (\Throwable) {
            // Column may not exist — non-blocking
        }

        return back()->withErrors([
            'email' => __('auth.failed'),
        ])->onlyInput('email');
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
