<?php

namespace App\Support;

use App\Models\Discount;
use InvalidArgumentException;

/** Exact money handling in integer minor units (kobo for NGN, francs for XOF). Never floats for amounts. */
final class Money
{
    /** @return array{discount_minor: int, total_minor: int} */
    public static function applyDiscount(int $subtotalMinor, string $currency, ?Discount $discount): array
    {
        if ($subtotalMinor < 0) {
            throw new InvalidArgumentException('Invalid subtotal');
        }
        $off = 0;
        if ($discount?->percent_off) {
            $off = intdiv($subtotalMinor * $discount->percent_off, 100);   // rounds down in the learner's favour
        } elseif ($discount?->amount_off_minor && $discount->currency === $currency) {
            $off = (int) $discount->amount_off_minor;
        }
        $off = min($off, $subtotalMinor);

        return ['discount_minor' => $off, 'total_minor' => $subtotalMinor - $off];
    }

    /** "₦180,000" / "180 000 FCFA" style display in the current language (see Currency::format). */
    public static function format(int $minor, string $currency = 'NGN'): string
    {
        return Currency::format($minor, $currency);
    }
}
