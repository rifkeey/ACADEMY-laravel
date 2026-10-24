<?php

use App\Support\Locales;

if (! function_exists('lroute')) {
    /**
     * route() for pages that exist in several languages: returns the current language's
     * version (for example /fr/programmes) and the English URL when there is none.
     */
    function lroute(string $name, mixed $parameters = [], bool $absolute = true): string
    {
        return route(Locales::routeName($name), $parameters, $absolute);
    }
}
