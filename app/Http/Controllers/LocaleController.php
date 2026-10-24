<?php

namespace App\Http\Controllers;

use App\Support\Locales;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * The EN | FR switcher. Saves the choice (cookie for everyone, profile for signed-in users) and
 * returns to the same page in the chosen language. It never touches the session or progress.
 */
class LocaleController extends Controller
{
    public function switch(Request $request, string $locale): RedirectResponse
    {
        abort_unless(Locales::isSupported($locale), 404);

        if ($user = $request->user()) {
            $user->forceFill(['locale' => $locale])->save();
        }
        $target = Locales::equivalentPath((string) $request->query('to', '/'), $locale);

        return redirect($target)->withCookie(cookie()->forever(Locales::COOKIE, $locale, sameSite: 'lax'));
    }
}
