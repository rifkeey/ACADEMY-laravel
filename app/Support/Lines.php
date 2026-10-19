<?php

namespace App\Support;

/** Lists edited in admin forms as "one item per line" text, stored as JSON arrays. */
final class Lines
{
    /** @return list<string> */
    public static function parse(?string $text, int $maxItems = 50, int $maxLength = 500): array
    {
        $items = array_values(array_filter(array_map('trim', preg_split('/\R/', (string) $text)), fn ($l) => $l !== ''));

        return array_map(fn ($l) => mb_substr($l, 0, $maxLength), array_slice($items, 0, $maxItems));
    }

    public static function join(?array $items): string
    {
        return implode("\n", $items ?? []);
    }
}
