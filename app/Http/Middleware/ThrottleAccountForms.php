<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Symfony\Component\HttpFoundation\Response;

/**
 * Rate limits for the account forms Fortify registers itself (it only limits login and 2FA):
 * sign-up and "forgot password" submissions, per IP address. Stops mass account creation and
 * reset-email flooding.
 */
class ThrottleAccountForms
{
    /** path => [max attempts, decay seconds] */
    private const LIMITS = [
        'register' => [10, 3600],
        'forgot-password' => [5, 900],
    ];

    public function handle(Request $request, Closure $next): Response
    {
        $limit = $request->isMethod('POST') ? (self::LIMITS[$request->path()] ?? null) : null;
        if (! $limit) {
            return $next($request);
        }
        [$max, $decay] = $limit;
        $key = 'account-form:'.$request->path().':'.$request->ip();
        if (RateLimiter::tooManyAttempts($key, $max)) {
            return back()->withInput($request->except('password', 'password_confirmation'))
                ->withErrors(['email' => __('Too many attempts. Please try again in :minutes minutes.', ['minutes' => (int) ceil(RateLimiter::availableIn($key) / 60)])]);
        }
        RateLimiter::hit($key, $decay);

        return $next($request);
    }
}
