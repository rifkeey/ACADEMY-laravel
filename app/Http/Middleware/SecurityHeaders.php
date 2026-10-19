<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Vite;
use Symfony\Component\HttpFoundation\Response;

/**
 * Security headers on every response. Scripts are restricted by a per-request nonce, which Vite
 * adds to the tags it renders, so no inline script runs unless it carries the nonce.
 */
class SecurityHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        Vite::useCspNonce();
        $nonce = Vite::cspNonce();

        $response = $next($request);

        $dev = app()->environment('local');
        $viteDev = $dev ? ' http://localhost:5173 http://127.0.0.1:5173 ws://localhost:5173 ws://127.0.0.1:5173' : '';

        $csp = implode('; ', [
            "default-src 'self'",
            "script-src 'self' 'nonce-{$nonce}'".$viteDev,
            "style-src 'self' 'unsafe-inline'".$viteDev,
            "font-src 'self' data:",
            "img-src 'self' data:",
            "connect-src 'self'".$viteDev,
            "frame-ancestors 'none'",
            "base-uri 'self'",
            "form-action 'self' https://checkout.paystack.com",
            "object-src 'none'",
        ]);

        $headers = [
            'Content-Security-Policy' => $csp,
            'X-Content-Type-Options' => 'nosniff',
            'X-Frame-Options' => 'DENY',
            'Referrer-Policy' => 'strict-origin-when-cross-origin',
            'Permissions-Policy' => 'camera=(), microphone=(), geolocation=(), payment=()',
        ];
        if ($request->isSecure()) {
            $headers['Strict-Transport-Security'] = 'max-age=63072000; includeSubDomains';
        }
        foreach ($headers as $k => $v) {
            $response->headers->set($k, $v);
        }
        $response->headers->remove('X-Powered-By');

        return $response;
    }
}
