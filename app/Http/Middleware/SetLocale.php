<?php

namespace App\Http\Middleware;

use App\Support\Locales;
use Carbon\Carbon;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Chooses the interface language for each request.
 *
 * 1. A language prefix in the URL (/fr/...) always wins.
 * 2. An English public page is shown in English, unless the visitor has explicitly chosen
 *    another language: then a GET request is redirected to that language's version. Search
 *    engines carry no preference, so they always see the page they asked for.
 * 3. Other pages (signed-in areas, which have one URL) use the saved preference: the user's
 *    profile, then the cookie, then the browser's Accept-Language, then English.
 *
 * Changing language never touches the session, so learners stay signed in and keep their progress.
 */
class SetLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        $default = Locales::default();
        $prefix = $request->segment(1);
        $urlLocale = $prefix !== $default && Locales::isSupported($prefix) ? $prefix : null;
        $preference = $this->preference($request);
        $routeName = $request->route()?->getName();

        if ($urlLocale) {
            $locale = $urlLocale;
        } elseif (Locales::isLocalisedRoute($routeName)) {
            if ($preference && $preference !== $default && $request->isMethod('GET')) {
                return redirect(Locales::equivalentPath($request->getRequestUri(), $preference));
            }
            $locale = $default;
        } else {
            $locale = $preference ?? Locales::fromHeader($request) ?? $default;
        }

        app()->setLocale($locale);
        Carbon::setLocale($locale);

        return $next($request);
    }

    /** An explicit choice: saved on the profile, or in the cookie set by the language switcher. */
    private function preference(Request $request): ?string
    {
        $saved = $request->user()?->locale;
        if (Locales::isSupported($saved)) {
            return $saved;
        }
        $cookie = $request->cookie(Locales::COOKIE);

        return Locales::isSupported($cookie) ? $cookie : null;
    }
}
