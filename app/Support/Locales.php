<?php

namespace App\Support;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * Supported interface languages and localised URLs.
 *
 * Public pages exist once per language: English keeps the original URLs and route names
 * ("programmes.show"); other languages are prefixed ("/fr/programmes/...", "fr.programmes.show").
 * Signed-in pages have a single URL and follow the user's saved preference.
 */
final class Locales
{
    public const COOKIE = 'locale';

    /** @return list<string> */
    public static function supported(): array
    {
        return array_keys(config('academy.locales'));
    }

    public static function isSupported(?string $locale): bool
    {
        return is_string($locale) && array_key_exists($locale, config('academy.locales'));
    }

    public static function default(): string
    {
        return config('academy.default_locale');
    }

    public static function htmlLang(?string $locale = null): string
    {
        return config('academy.locales.'.($locale ?? app()->getLocale()).'.html', 'en');
    }

    /** The route name to use for $name in $locale, falling back to the English route. */
    public static function routeName(string $name, ?string $locale = null): string
    {
        $locale ??= app()->getLocale();
        $base = self::baseName($name);
        if ($locale !== self::default() && Route::has("{$locale}.{$base}")) {
            return "{$locale}.{$base}";
        }

        return $base;
    }

    /** Strips a language prefix from a route name: "fr.programmes.show" → "programmes.show". */
    public static function baseName(string $name): string
    {
        foreach (self::supported() as $l) {
            if ($l !== self::default() && str_starts_with($name, "{$l}.")) {
                return substr($name, strlen($l) + 1);
            }
        }

        return $name;
    }

    /** True when the named route has a version in more than one language (a public page). */
    public static function isLocalisedRoute(?string $name): bool
    {
        if (! $name) {
            return false;
        }
        $base = self::baseName($name);
        foreach (self::supported() as $l) {
            if ($l !== self::default() && Route::has("{$l}.{$base}")) {
                return true;
            }
        }

        return false;
    }

    /**
     * The equivalent of a same-site path in another language. Public pages map to their
     * counterpart; signed-in pages keep their path (their language comes from the preference).
     * Anything that is not a safe relative path falls back to the home page.
     */
    /** The language a site path is in, from its prefix ("/fr/programmes" → "fr"); unprefixed paths are the default language. */
    public static function fromPath(string $path): string
    {
        $first = explode('/', ltrim((string) parse_url($path, PHP_URL_PATH), '/'))[0];

        return $first !== self::default() && self::isSupported($first) ? $first : self::default();
    }

    public static function equivalentPath(string $path, string $locale): string
    {
        $home = route(self::routeName('home', $locale), [], false);
        if ($path === '' || $path[0] !== '/' || str_starts_with($path, '//') || str_contains($path, '\\')) {
            return $home;
        }
        [$pathOnly, $query] = array_pad(explode('?', $path, 2), 2, null);
        try {
            $route = Route::getRoutes()->match(Request::create($pathOnly, 'GET'));
        } catch (HttpException) {
            return $home;
        }
        $name = $route->getName();
        if (! self::isLocalisedRoute($name)) {
            return $path;
        }
        $url = route(self::routeName($name, $locale), $route->parameters(), false);

        return $query !== null ? "{$url}?{$query}" : $url;
    }

    /** First supported language in the browser's Accept-Language header, if any. */
    public static function fromHeader(Request $request): ?string
    {
        foreach ($request->getLanguages() as $lang) {
            $code = strtolower(substr($lang, 0, 2));
            if (self::isSupported($code)) {
                return $code;
            }
        }

        return null;
    }
}
