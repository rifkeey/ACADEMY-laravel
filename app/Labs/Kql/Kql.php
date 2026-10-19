<?php

namespace App\Labs\Kql;

/**
 * A small, safe interpreter for a teaching subset of Kusto Query Language (KQL).
 *
 * It runs entirely in memory over the synthetic datasets supplied with a lab: no file access,
 * no network, no eval. Supported: where, project, extend, summarize (count, dcount, min, max,
 * sum, avg, make_set) with `by` and bin(), order/sort by, take/limit, top, distinct, count;
 * operators ==, !=, =~, !~, <, <=, >, >=, contains, !contains, has, !has, startswith,
 * endswith, in, !in, in~, !in~, and/or/not, + - * /; functions ago(), now(), datetime(), bin(),
 * tolower(), toupper(), strlen(), isempty(), isnotempty(), tostring(), toint().
 *
 * Not supported (learners are told): join, union, let, parse, mv-expand, render.
 *
 * Behaviour deliberately matches the original TypeScript implementation, so lab answers and
 * marking are unchanged after the migration.
 */
final class Kql
{
    public const MAX_ROWS = 500;

    public const MAX_QUERY_LENGTH = 4000;

    private int $now;

    private function __construct(string $now)
    {
        $this->now = KqlDate::parse($now) ?? 0;
    }

    /**
     * Executes $query against $tables (each ['name', 'description', 'columns', 'rows']).
     * $now anchors ago() so results are deterministic.
     *
     * @return array{columns: list<string>, rows: list<array<string, mixed>>, truncated: bool}
     */
    public static function run(string $query, array $tables, string $now): array
    {
        if (strlen($query) > self::MAX_QUERY_LENGTH) {
            throw new KqlError('Query is too long (4,000 character limit)');
        }

        return (new self($now))->execute(Parser::parse($query), $tables);
    }

    private function execute(array $parsed, array $tables): array
    {
        $table = null;
        foreach ($tables as $t) {
            if ($t['name'] === $parsed['table']) {
                $table = $t;
            }
        }
        if (! $table) {
            $similar = null;
            foreach ($tables as $t) {
                if (strtolower($t['name']) === strtolower($parsed['table'])) {
                    $similar = $t['name'];
                }
            }
            $hint = $similar ? " Did you mean '{$similar}'?" : ' Available: '.implode(', ', array_column($tables, 'name'));
            throw new KqlError("Unknown table '{$parsed['table']}'.".$hint, 0);
        }

        $columns = $table['columns'];
        $rows = $table['rows'];

        foreach ($parsed['ops'] as $op) {
            switch ($op['k']) {
                case 'where':
                    $rows = array_values(array_filter($rows, fn ($r) => $this->truthy($this->eval($op['e'], $r))));
                    break;

                case 'project':
                    $rows = array_map(function ($r) use ($op) {
                        $out = [];
                        foreach ($op['items'] as $it) {
                            $out[$it['alias']] = $this->eval($it['e'], $r);
                        }

                        return $out;
                    }, $rows);
                    $columns = array_column($op['items'], 'alias');
                    break;

                case 'extend':
                    $rows = array_map(function ($r) use ($op) {
                        foreach ($op['items'] as $it) {
                            $r[$it['alias']] = $this->eval($it['e'], $r);
                        }

                        return $r;
                    }, $rows);
                    $aliases = array_column($op['items'], 'alias');
                    $columns = [...array_values(array_diff($columns, $aliases)), ...$aliases];
                    break;

                case 'summarize':
                    [$rows, $columns] = $this->summarize($op, $rows);
                    break;

                case 'order':
                    usort($rows, function ($a, $b) use ($op) {   // usort is stable in PHP 8+
                        foreach ($op['items'] as $it) {
                            $c = $this->compare($this->eval($it['e'], $a), $this->eval($it['e'], $b));
                            if ($c != 0) {
                                return $it['desc'] ? -$this->sign($c) : $this->sign($c);
                            }
                        }

                        return 0;
                    });
                    break;

                case 'take':
                    $rows = array_slice($rows, 0, $op['n']);
                    break;

                case 'top':
                    usort($rows, function ($a, $b) use ($op) {
                        $c = $this->sign($this->compare($this->eval($op['e'], $a), $this->eval($op['e'], $b)));

                        return $op['desc'] ? -$c : $c;
                    });
                    $rows = array_slice($rows, 0, $op['n']);
                    break;

                case 'distinct':
                    foreach ($op['cols'] as $c) {
                        if (! in_array($c, $columns, true)) {
                            throw new KqlError("Unknown column '{$c}'");
                        }
                    }
                    $seen = [];
                    $out = [];
                    foreach ($rows as $r) {
                        $picked = [];
                        foreach ($op['cols'] as $c) {
                            $picked[$c] = $r[$c] ?? null;
                        }
                        $key = json_encode(array_map(fn ($v) => $this->str($v), array_values($picked)));
                        if (! isset($seen[$key])) {
                            $seen[$key] = true;
                            $out[] = $picked;
                        }
                    }
                    $rows = $out;
                    $columns = $op['cols'];
                    break;

                case 'count':
                    $rows = [['Count' => count($rows)]];
                    $columns = ['Count'];
                    break;
            }
        }

        $truncated = count($rows) > self::MAX_ROWS;
        $out = [];
        foreach (array_slice($rows, 0, self::MAX_ROWS) as $r) {
            $row = [];
            foreach ($columns as $c) {
                $v = $r[$c] ?? null;
                $row[$c] = $v instanceof KqlDate ? $v->iso() : $v;
            }
            $out[] = $row;
        }

        return ['columns' => array_values($columns), 'rows' => $out, 'truncated' => $truncated];
    }

    private function summarize(array $op, array $rows): array
    {
        $groups = [];
        foreach ($rows as $r) {
            $key = [];
            foreach ($op['by'] as $b) {
                $key[$b['alias']] = $this->eval($b['e'], $r);
            }
            $k = json_encode(array_map(fn ($v) => $this->str($v), array_values($key)));
            $groups[$k] ??= ['key' => $key, 'rows' => []];
            $groups[$k]['rows'][] = $r;
        }
        if (! $op['by'] && ! $groups) {
            $groups['[]'] = ['key' => [], 'rows' => []];   // summarize count() over no rows → one row with 0
        }

        $out = [];
        foreach ($groups as $g) {
            $row = $g['key'];
            foreach ($op['aggs'] as $a) {
                $row[$this->aggName($a)] = $this->aggregate($a, $g['rows']);
            }
            $out[] = $row;
        }

        return [$out, [...array_column($op['by'], 'alias'), ...array_map(fn ($a) => $this->aggName($a), $op['aggs'])]];
    }

    private function aggregate(array $agg, array $rows): mixed
    {
        $vals = fn () => array_map(fn ($r) => $this->eval($agg['arg'], $r), $rows);

        switch ($agg['fn']) {
            case 'count':
                return count($rows);
            case 'dcount':
                return count(array_unique(array_map(fn ($v) => $this->str($v), $vals())));
            case 'min':
            case 'max':
                $best = null;
                foreach ($vals() as $v) {
                    $c = $best === null ? 0 : $this->compare($v, $best);
                    if ($best === null || ($agg['fn'] === 'min' ? $c < 0 : $c > 0)) {
                        $best = $v;
                    }
                }

                return $best;
            case 'sum':
                return array_sum(array_map(fn ($v) => $this->num($v ?? 0), $vals()));
            case 'avg':
                $v = $vals();

                return $v ? array_sum(array_map(fn ($x) => $this->num($x ?? 0), $v)) / count($v) : null;
            case 'make_set':
                $set = array_values(array_unique(array_map(fn ($v) => $this->str($v), $vals())));
                sort($set, SORT_STRING);

                return json_encode($set, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        }
        throw new KqlError("Unsupported aggregation '{$agg['fn']}'", $agg['p']);
    }

    private function aggName(array $a): string
    {
        if ($a['alias']) {
            return $a['alias'];
        }
        $col = ($a['arg']['k'] ?? null) === 'col' ? $a['arg']['name'] : '';

        return match ($a['fn']) {
            'count' => 'count_',
            'make_set' => "set_{$col}",
            default => "{$a['fn']}_{$col}",
        };
    }

    // ---- expression evaluation -----------------------------------------

    private function eval(array $e, array $row): mixed
    {
        switch ($e['k']) {
            case 'lit':
            case 'span':
                return $e['v'];

            case 'col':
                if (! array_key_exists($e['name'], $row)) {
                    $similar = null;
                    foreach (array_keys($row) as $c) {
                        if (strtolower($c) === strtolower($e['name'])) {
                            $similar = $c;
                        }
                    }
                    throw new KqlError("Unknown column '{$e['name']}'".($similar ? ". Column names are case-sensitive — did you mean '{$similar}'?" : ''), $e['p']);
                }

                return $row[$e['name']];

            case 'not':
                return ! $this->truthy($this->eval($e['e'], $row));

            case 'in':
                $l = $this->eval($e['l'], $row);
                $found = false;
                foreach ($e['list'] as $x) {
                    $r = $this->eval($x, $row);
                    $match = $e['ci'] ? mb_strtolower($this->str($l)) === mb_strtolower($this->str($r)) : $this->compare($l, $r) == 0;
                    if ($match) {
                        $found = true;
                        break;
                    }
                }

                return $e['neg'] ? ! $found : $found;

            case 'call':
                return $this->call($e, $row);

            case 'bin':
                return $this->binary($e, $row);
        }
        throw new KqlError('Unsupported expression');
    }

    private function binary(array $e, array $row): mixed
    {
        if ($e['op'] === 'and') {
            return $this->truthy($this->eval($e['l'], $row)) && $this->truthy($this->eval($e['r'], $row));
        }
        if ($e['op'] === 'or') {
            return $this->truthy($this->eval($e['l'], $row)) || $this->truthy($this->eval($e['r'], $row));
        }
        $l = $this->eval($e['l'], $row);
        $r = $this->eval($e['r'], $row);
        $ls = mb_strtolower($this->str($l));
        $rs = mb_strtolower($this->str($r));

        switch ($e['op']) {
            case '==': return $this->strictEquals($l, $r);
            case '!=': return ! $this->strictEquals($l, $r);
            case '=~': return $ls === $rs;
            case '!~': return $ls !== $rs;
            case '<': return $this->compare($l, $r) < 0;
            case '<=': return $this->compare($l, $r) <= 0;
            case '>': return $this->compare($l, $r) > 0;
            case '>=': return $this->compare($l, $r) >= 0;
            case 'contains': return str_contains($ls, $rs);
            case '!contains': return ! str_contains($ls, $rs);
            case 'has': return $this->hasTerm($ls, $rs);
            case '!has': return ! $this->hasTerm($ls, $rs);
            case 'startswith': return str_starts_with($ls, $rs);
            case 'endswith': return str_ends_with($ls, $rs);
            case '+':
            case '-':
            case '*':
            case '/':
                // Datetime arithmetic: datetime ± timespan.
                $lt = $this->toTime($l);
                if ($l instanceof KqlDate || ($lt !== null && (is_int($r) || is_float($r)) && ($e['op'] === '+' || $e['op'] === '-'))) {
                    return new KqlDate((int) (($lt ?? 0) + ($e['op'] === '+' ? $this->num($r) : -$this->num($r))));
                }
                $a = $this->num($l);
                $b = $this->num($r);

                return match ($e['op']) {
                    '+' => $a + $b,
                    '-' => $a - $b,
                    '*' => $a * $b,
                    '/' => $b == 0 ? null : $a / $b,
                };
        }
        throw new KqlError("Unsupported operator '{$e['op']}'");
    }

    private function call(array $e, array $row): mixed
    {
        $args = array_map(fn ($a) => $this->eval($a, $row), $e['args']);

        switch ($e['name']) {
            case 'ago':
                if (! is_int($args[0] ?? null) && ! is_float($args[0] ?? null)) {
                    throw new KqlError('ago() expects a timespan such as 1h or 7d', $e['p']);
                }

                return new KqlDate((int) ($this->now - $args[0]));
            case 'now':
                return new KqlDate($this->now);
            case 'datetime':
                $t = KqlDate::parse($this->str($args[0] ?? null));
                if ($t === null) {
                    throw new KqlError("Invalid datetime '{$this->str($args[0] ?? null)}'", $e['p']);
                }

                return new KqlDate($t);
            case 'bin':
                $size = $this->num($args[1] ?? null);
                if (! $size || is_nan($size)) {
                    throw new KqlError('bin() needs a bin size', $e['p']);
                }
                $t = $this->toTime($args[0] ?? null);
                if ($t !== null) {
                    return new KqlDate((int) (floor($t / $size) * $size));
                }

                return floor($this->num($args[0] ?? null) / $size) * $size;
            case 'tolower':
                return mb_strtolower($this->str($args[0] ?? null));
            case 'toupper':
                return mb_strtoupper($this->str($args[0] ?? null));
            case 'strlen':
                return mb_strlen($this->str($args[0] ?? null));
            case 'isempty':
                return ($args[0] ?? null) === null || $this->str($args[0]) === '';
            case 'isnotempty':
                return ! (($args[0] ?? null) === null || $this->str($args[0]) === '');
            case 'tostring':
                return $this->str($args[0] ?? null);
            case 'toint':
                return preg_match('/^\s*([+-]?\d+)/', $this->str($args[0] ?? null), $m) ? (int) $m[1] : null;
        }
        throw new KqlError("Unknown function '{$e['name']}()'", $e['p']);
    }

    // ---- value semantics (mirror JavaScript so results match the original) ----

    /** Equality used by == and !=: like compare(), but two strings must match exactly. */
    private function strictEquals(mixed $l, mixed $r): bool
    {
        if (is_string($l) && is_string($r)) {
            return $l === $r;
        }

        return $this->compare($l, $r) == 0;
    }

    private function truthy(mixed $v): bool
    {
        return $v === true || ((is_int($v) || is_float($v)) && $v != 0) || (is_string($v) && strtolower($v) === 'true');
    }

    private function hasTerm(string $haystackLower, string $termLower): bool
    {
        return in_array($termLower, preg_split('/[^a-z0-9_]+/', $haystackLower), true);
    }

    /** String form of a value: null → '', dates → ISO, numbers as JavaScript would print them. */
    private function str(mixed $v): string
    {
        return match (true) {
            $v === null => '',
            $v instanceof KqlDate => $v->iso(),
            $v === true => 'true',
            $v === false => 'false',
            is_float($v) && floor($v) === $v && abs($v) < 1e15 => (string) (int) $v,
            default => (string) $v,
        };
    }

    /** JavaScript Number(): '' → 0, numeric strings → number, everything else → NaN. */
    private function num(mixed $v): float|int
    {
        if (is_int($v) || is_float($v)) {
            return $v;
        }
        if (is_bool($v)) {
            return $v ? 1 : 0;
        }
        if ($v === null) {
            return 0;
        }
        if ($v instanceof KqlDate) {
            return $v->ms;
        }
        $s = trim((string) $v);
        if ($s === '') {
            return 0;
        }

        return is_numeric($s) ? $s + 0 : NAN;
    }

    private function toTime(mixed $v): ?int
    {
        if ($v instanceof KqlDate) {
            return $v->ms;
        }

        return is_string($v) ? KqlDate::parse($v) : null;
    }

    /** Ordering used by comparisons, sorting, min and max. Returns <0, 0 or >0. */
    private function compare(mixed $a, mixed $b): float|int
    {
        if ($a === null && $b === null) {
            return 0;
        }
        if ($a === null) {
            return -1;
        }
        if ($b === null) {
            return 1;
        }
        $ta = $this->toTime($a);
        $tb = $this->toTime($b);
        if (($a instanceof KqlDate || $b instanceof KqlDate) && $ta !== null && $tb !== null) {
            return $ta - $tb;
        }
        if ((is_int($a) || is_float($a)) && (is_int($b) || is_float($b))) {
            return $a - $b;
        }
        if ($ta !== null && $tb !== null) {
            return $ta - $tb;
        }
        if (is_int($a) || is_float($a) || is_int($b) || is_float($b)) {
            $na = $this->num($a);
            $nb = $this->num($b);
            if (! is_nan($na) && ! is_nan($nb)) {
                return $na - $nb;
            }
        }

        return strcmp($this->str($a), $this->str($b));
    }

    private function sign(float|int $n): int
    {
        return $n <=> 0;
    }
}
