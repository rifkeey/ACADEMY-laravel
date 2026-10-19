<?php

namespace App\Support;

/** Random, unguessable public identifiers for certificates and badges. */
final class CredentialIds
{
    // Crockford base32: no I, L, O or U, so IDs are easy to read aloud and type.
    private const ALPHABET = '0123456789ABCDEFGHJKMNPQRSTVWXYZ';

    public const PATTERN = '/^[0-9A-HJKMNP-TV-Z]{26}$/';

    /** 26 characters × 5 bits = 130 bits of randomness from the OS CSPRNG. */
    public static function credentialId(): string
    {
        return self::random(26);
    }

    /** Human-readable certificate number, e.g. AMQ-SOC-2026-7KQ4M2XR (random suffix, not sequential). */
    public static function certificateNumber(string $code, ?int $year = null): string
    {
        return sprintf('AMQ-%s-%d-%s', $code, $year ?? (int) gmdate('Y'), self::random(8));
    }

    /** Order reference sent to the payment provider. */
    public static function orderReference(): string
    {
        return 'AMQ-'.strtoupper(base_convert((string) time(), 10, 36)).'-'.self::random(10);
    }

    private static function random(int $length): string
    {
        $bytes = random_bytes($length);
        $out = '';
        for ($i = 0; $i < $length; $i++) {
            $out .= self::ALPHABET[ord($bytes[$i]) % 32];   // 256 is a multiple of 32: no bias
        }

        return $out;
    }
}
