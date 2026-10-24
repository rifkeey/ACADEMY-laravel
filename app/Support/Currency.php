<?php

namespace App\Support;

use App\Models\User;
use Illuminate\Http\Request;
use InvalidArgumentException;

/**
 * Selling currencies: minor units, exact parsing, display and the visitor's chosen currency.
 *
 * Amounts are integers in each currency's ISO 4217 minor unit: kobo for NGN (2 decimals) and
 * whole francs for XOF (0 decimals). Never use floats for amounts.
 */
final class Currency
{
    public const COOKIE = 'currency';

    /** @return list<string> */
    public static function supported(): array
    {
        return array_keys(config('academy.currencies'));
    }

    public static function isSupported(?string $code): bool
    {
        return is_string($code) && array_key_exists($code, config('academy.currencies'));
    }

    public static function decimals(string $code): int
    {
        return (int) self::meta($code)['decimals'];
    }

    public static function short(string $code): string
    {
        return self::meta($code)['short'];
    }

    /**
     * "180000.50" (major units, as typed) → 18000050 minor units, using string arithmetic so no
     * rounding can happen. Returns null for anything malformed or with too many decimals.
     */
    public static function parseMajor(string $amount, string $code): ?int
    {
        $d = self::decimals($code);
        $pattern = $d > 0 ? '/^\d{1,9}(\.\d{1,'.$d.'})?$/' : '/^\d{1,11}$/';
        $amount = str_replace([' ', "\u{00A0}", "\u{202F}", ','], '', trim($amount));
        if (! preg_match($pattern, $amount)) {
            return null;
        }
        [$whole, $frac] = array_pad(explode('.', $amount, 2), 2, '');

        return (int) $whole * (10 ** $d) + ($d > 0 ? (int) str_pad($frac, $d, '0') : 0);
    }

    /** Minor units → major-unit decimal string ("18000050" → "180000.50"), exact. */
    public static function toMajorString(int $minor, string $code): string
    {
        $d = self::decimals($code);

        return $d === 0 ? (string) $minor : bcdiv((string) $minor, (string) (10 ** $d), $d);
    }

    /**
     * Display text, for example "₦180,000" in English, "180 000 FCFA" in French. Decimals are
     * shown only when the amount has a fractional part.
     */
    public static function format(int $minor, string $code, ?string $locale = null): string
    {
        $locale ??= app()->getLocale();
        $d = self::decimals($code);
        $showDecimals = $d > 0 && $minor % (10 ** $d) !== 0;
        [$whole, $frac] = array_pad(explode('.', self::toMajorString(abs($minor), $code), 2), 2, '');
        $fr = $locale === 'fr';
        // Group the digits first and add the separator last: it is multibyte, so it must never be reversed.
        $groups = array_reverse(array_map('strrev', str_split(strrev($whole), 3)));
        $grouped = implode($fr ? "\u{202F}" : ',', $groups);
        $number = $grouped.($showDecimals ? ($fr ? ',' : '.').$frac : '');
        $sign = $minor < 0 ? '−' : '';

        // French puts the symbol after the amount; English keeps ₦ in front and FCFA after.
        return match (true) {
            $code === 'NGN' && ! $fr => $sign.'₦'.$number,
            default => $sign.$number."\u{00A0}".self::meta($code)['symbol'],
        };
    }

    /** The currency prices are shown and charged in for this visitor. */
    public static function current(?Request $request = null): string
    {
        $request ??= request();
        $user = $request->user();
        if ($user instanceof User && self::isSupported($user->currency)) {
            return $user->currency;
        }
        $cookie = $request->cookie(self::COOKIE);
        if (self::isSupported($cookie)) {
            return $cookie;
        }

        return self::forLocale(app()->getLocale());
    }

    /** Default currency for a language (English → NGN, French → XOF). */
    public static function forLocale(string $locale): string
    {
        $code = config("academy.locales.{$locale}.currency");

        return self::isSupported($code) ? $code : self::supported()[0];
    }

    /**
     * Suggested price in $to from a price in $from and an owner-entered rate (1 $from = $rate $to),
     * rounded to the nearest $step major units. Exact decimal arithmetic (bcmath). Only a suggestion:
     * nothing is saved until an administrator confirms it.
     */
    public static function suggest(int $fromMinor, string $from, string $rate, string $to, int $step): int
    {
        if (! preg_match('/^\d{1,6}(\.\d{1,8})?$/', $rate) || bccomp($rate, '0', 8) <= 0 || $step < 1) {
            throw new InvalidArgumentException('Invalid rate or rounding step');
        }
        $major = bcmul(self::toMajorString($fromMinor, $from), $rate, 8);
        // Round half up to the nearest step.
        $steps = bcdiv(bcadd($major, bcdiv((string) $step, '2', 8), 8), (string) $step, 0);
        $roundedMajor = bcmul($steps, (string) $step, 0);

        return (int) bcmul($roundedMajor, (string) (10 ** self::decimals($to)), 0);
    }

    private static function meta(string $code): array
    {
        return config("academy.currencies.{$code}") ?? throw new InvalidArgumentException("Unsupported currency {$code}");
    }
}
