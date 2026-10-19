<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Admin area gate. Non-staff receive a 404 rather than confirmation that the area exists.
 * In production, staff must also have confirmed two-factor authentication.
 */
class EnsureStaff
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        abort_unless($user && $user->isStaff(), 404);

        if (config('academy.require_staff_two_factor') && ! $user->hasEnabledTwoFactorAuthentication()) {
            return redirect()->route('account.security')
                ->with('warning', 'Administrators must turn on two-factor authentication before using the admin area.');
        }

        return $next($request);
    }
}
