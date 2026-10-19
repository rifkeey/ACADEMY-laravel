<?php

namespace App\Labs\Kql;

/** A datetime value inside the interpreter, stored as UTC epoch milliseconds. */
final class KqlDate
{
    public function __construct(public readonly int $ms) {}

    /** Same format as JavaScript's Date.toISOString(), e.g. 2026-03-04T01:00:00.000Z. */
    public function iso(): string
    {
        $seconds = intdiv($this->ms, 1000) - ($this->ms % 1000 < 0 ? 1 : 0);
        $millis = (($this->ms % 1000) + 1000) % 1000;

        return gmdate('Y-m-d\TH:i:s', $seconds).sprintf('.%03dZ', $millis);
    }

    /** Parses ISO-like date strings (as used in lab datasets); returns null when not a date. */
    public static function parse(string $value): ?int
    {
        $value = trim($value);
        if (! preg_match('/^\d{4}-\d{2}-\d{2}/', $value)) {
            return null;
        }
        try {
            $dt = new \DateTimeImmutable($value, new \DateTimeZone('UTC'));
        } catch (\Exception) {
            return null;
        }

        return (int) $dt->format('U') * 1000 + (int) $dt->format('v');
    }
}
