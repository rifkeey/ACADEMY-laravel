<?php

use App\Exceptions\RuleViolation;
use App\Http\Middleware\EnsureAccountActive;
use App\Http\Middleware\EnsureStaff;
use App\Http\Middleware\SecurityHeaders;
use App\Http\Middleware\ThrottleAccountForms;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->web(append: [SecurityHeaders::class, EnsureAccountActive::class, ThrottleAccountForms::class]);

        $middleware->alias(['staff' => EnsureStaff::class]);

        // Payment providers post webhooks without a session; they are authenticated by signature instead.
        $middleware->validateCsrfTokens(except: ['webhooks/*']);

        // No proxies are trusted by default: on cPanel hosting PHP is served directly, and trusting
        // forwarded headers would let clients spoof their IP address. If the host adds a proxy or
        // CDN in front, list its addresses here (see docs/DEPLOYMENT_GO54.md).
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Business-rule errors carry a message written for the user: show it instead of a 500.
        $exceptions->render(function (RuleViolation $e, Request $request) {
            return $request->expectsJson()
                ? response()->json(['message' => $e->getMessage()], 422)
                : back()->with('error', $e->getMessage())->withInput();
        });
    })->create();
