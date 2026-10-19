<?php

namespace App\Assessment;

use Random\Randomizer;

/**
 * Examination grading rules. Integer arithmetic only, so the pass mark can never be affected by
 * floating-point rounding: pass ⇔ correct × 100 ≥ passPercent × total.
 * With 40 questions and a 75% pass mark, 30 correct passes and 29 fails.
 */
final class Grading
{
    public static function isPass(int $correct, int $total, int $passPercent): bool
    {
        return $total > 0 && $correct * 100 >= $passPercent * $total;
    }

    /** Percentage rounded down to one decimal place, for display only. */
    public static function percent(int $correct, int $total): float
    {
        return $total > 0 ? floor(($correct * 1000) / $total) / 10 : 0.0;
    }

    /** All-or-nothing: the selected set must equal the correct set exactly. */
    public static function isAnswerCorrect(array $correctIds, ?array $selected): bool
    {
        if (! $selected) {
            return false;
        }
        $selected = array_values(array_unique($selected));

        return count($selected) === count($correctIds) && ! array_diff($correctIds, $selected);
    }

    /**
     * Picks $count questions spread across categories as evenly as possible, so each attempt
     * covers the whole curriculum, then shuffles their order.
     *
     * @param  list<array{category: string}>|list<object>  $pool
     */
    public static function selectBalanced(array $pool, int $count, ?Randomizer $random = null): array
    {
        $random ??= new Randomizer;   // CSPRNG-backed by default
        $byCategory = [];
        foreach ($random->shuffleArray($pool) as $q) {
            $byCategory[is_array($q) ? $q['category'] : $q->category][] = $q;
        }
        $queues = $random->shuffleArray(array_values($byCategory));
        $out = [];
        while (count($out) < $count && array_filter($queues)) {
            foreach ($queues as &$queue) {
                if (count($out) >= $count) {
                    break;
                }
                if ($queue) {
                    $out[] = array_shift($queue);
                }
            }
            unset($queue);
        }

        return $random->shuffleArray($out);
    }
}
