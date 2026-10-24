<?php

namespace App\Http\Controllers;

use App\Support\Currency;
use App\Support\Locales;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * NGN | FCFA switcher. Saves the visitor's currency (cookie, and the profile when signed in) and
 * returns to the same page. It only changes which configured price is shown and charged; it never
 * converts amounts.
 */
class CurrencyController extends Controller
{
    public function switch(Request $request, string $currency): RedirectResponse
    {
        abort_unless(Currency::isSupported($currency), 404);

        if ($user = $request->user()) {
            $user->forceFill(['currency' => $currency])->save();
        }
        // Back to the same page in the language it was in (this route has no /fr prefix of its own);
        // equivalentPath refuses anything that is not a path on this site.
        $to = (string) $request->query('to', '/');
        $target = Locales::equivalentPath($to, Locales::fromPath($to));

        return redirect($target)->withCookie(cookie()->forever(Currency::COOKIE, $currency, sameSite: 'lax'));
    }
}
