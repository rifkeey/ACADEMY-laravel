<?php

namespace App\Labs\Kql;

/**
 * Turns query text into tokens: identifiers, strings, numbers, timespans (5m, 1h, 7d) and operators.
 * Each token is ['t' => type, 'v' => value, 'p' => position].
 */
final class Lexer
{
    private const SPAN_UNITS = ['ms' => 1, 's' => 1000, 'm' => 60_000, 'h' => 3_600_000, 'd' => 86_400_000];

    // Longest operators first so "==" is not read as "=" then "=".
    private const OPS = ['==', '!=', '=~', '!~', '<=', '>=', '<', '>', '=', '|', ',', '(', ')', '+', '-', '*', '/'];

    /** @return list<array{t: string, v?: mixed, p: int}> */
    public static function tokenize(string $src): array
    {
        $tokens = [];
        $len = strlen($src);
        $i = 0;

        while ($i < $len) {
            $c = $src[$i];
            $rest = substr($src, $i);

            if (ctype_space($c)) {
                $i++;

                continue;
            }

            // Line comment: // ...
            if ($c === '/' && ($src[$i + 1] ?? '') === '/') {
                while ($i < $len && $src[$i] !== "\n") {
                    $i++;
                }

                continue;
            }

            // String literal in single or double quotes, with backslash escapes.
            if ($c === '"' || $c === "'") {
                $start = $i++;
                $s = '';
                while ($i < $len && $src[$i] !== $c) {
                    if ($src[$i] === '\\' && $i + 1 < $len) {
                        $s .= $src[$i + 1];
                        $i += 2;
                    } else {
                        $s .= $src[$i++];
                    }
                }
                if (($src[$i] ?? null) !== $c) {
                    throw new KqlError('Unterminated string literal', $start);
                }
                $i++;
                $tokens[] = ['t' => 'string', 'v' => $s, 'p' => $start];

                continue;
            }

            // Number, optionally followed by a timespan unit (5m, 1h, 500ms).
            if (ctype_digit($c)) {
                $start = $i;
                while ($i < $len && preg_match('/[0-9.]/', $src[$i])) {
                    $i++;
                }
                $num = (float) substr($src, $start, $i - $start);
                $num = floor($num) === $num ? (int) $num : $num;
                if (preg_match('/^(ms|s|m|h|d)\b/', substr($src, $i), $u)) {
                    $i += strlen($u[1]);
                    $tokens[] = ['t' => 'timespan', 'v' => $num * self::SPAN_UNITS[$u[1]], 'p' => $start];
                } else {
                    $tokens[] = ['t' => 'number', 'v' => $num, 'p' => $start];
                }

                continue;
            }

            // datetime(2026-03-04T10:00:00Z): the literal inside is taken as raw text.
            if (preg_match('/^datetime\s*\(([^)]*)\)/i', $rest, $dt)) {
                array_push($tokens,
                    ['t' => 'ident', 'v' => 'datetime', 'p' => $i],
                    ['t' => 'op', 'v' => '(', 'p' => $i],
                    ['t' => 'string', 'v' => trim($dt[1]), 'p' => $i],
                    ['t' => 'op', 'v' => ')', 'p' => $i],
                );
                $i += strlen($dt[0]);

                continue;
            }

            // Case-insensitive list membership: in~ and !in~
            if (preg_match('/^!?in~/', $rest, $m) && ! preg_match('/[A-Za-z0-9_]/', $i > 0 ? $src[$i - 1] : '')) {
                $tokens[] = ['t' => 'op', 'v' => $m[0], 'p' => $i];
                $i += strlen($m[0]);

                continue;
            }

            // Identifier (table, column, keyword or function name).
            if (preg_match('/[A-Za-z_]/', $c)) {
                $start = $i;
                while ($i < $len && preg_match('/[A-Za-z0-9_-]/', $src[$i])) {
                    // '-' is only allowed for the (unsupported) mv-expand keyword.
                    if ($src[$i] === '-' && substr($src, $start, $i - $start) !== 'mv') {
                        break;
                    }
                    $i++;
                }
                $tokens[] = ['t' => 'ident', 'v' => substr($src, $start, $i - $start), 'p' => $start];

                continue;
            }

            // Negated word operators: !contains, !has, !in
            if ($c === '!' && preg_match('/^!(contains|has|in)\b/', $rest, $m)) {
                $tokens[] = ['t' => 'op', 'v' => $m[0], 'p' => $i];
                $i += strlen($m[0]);

                continue;
            }

            foreach (self::OPS as $op) {
                if (str_starts_with($rest, $op)) {
                    $tokens[] = ['t' => 'op', 'v' => $op, 'p' => $i];
                    $i += strlen($op);

                    continue 2;
                }
            }

            throw new KqlError("Unexpected character '{$c}'", $i);
        }

        $tokens[] = ['t' => 'eof', 'p' => $len];

        return $tokens;
    }
}
