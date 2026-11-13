<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware: Ensure the user has completed the 2FA challenge.
 *
 * Plan reference: Feature 1.5 — MFA + 2FA
 * Plan T1.5-A: 2FA required after enabling.
 */
class EnsureTwoFactorVerified
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            return $next($request);
        }

        // If 2FA is enabled but not yet verified in this session
        if ($user->hasTwoFactorEnabled() && ! $request->session()->get('two_factor_verified')) {
            // Allow the challenge and setup routes through
            if ($request->routeIs('two-factor.*', 'logout')) {
                return $next($request);
            }

            return redirect()->route('two-factor.challenge');
        }

        return $next($request);
    }
}
