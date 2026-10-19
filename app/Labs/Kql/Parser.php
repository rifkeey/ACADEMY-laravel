<?php

namespace App\Labs\Kql;

/**
 * Recursive-descent parser for the supported KQL subset.
 *
 * Grammar (simplified):
 *   query    := Table ('|' operator)*
 *   operator := where expr | project items | extend items | summarize aggs [by items]
 *             | order/sort by expr [asc|desc], ... | take/limit n | top n by expr | distinct cols | count
 *   expr     := or;  or := and ('or' and)*;  and := cmp ('and' cmp)*
 *   cmp      := add [(comparison | in | !in | in~ | !in~) ...];  add := primary (('+'|'-'|'*'|'/') primary)*
 *
 * Expressions are arrays with a 'k' kind: lit, span, col, call, bin, in, not.
 */
final class Parser
{
    private const COMPARISON = ['==', '!=', '=~', '!~', '<', '<=', '>', '>=', 'contains', '!contains', 'has', '!has', 'startswith', 'endswith'];

    private const AGGREGATES = ['count', 'dcount', 'min', 'max', 'sum', 'avg', 'make_set'];

    private const UNSUPPORTED = ['join', 'union', 'let', 'parse', 'render', 'mv-expand'];

    private int $i = 0;

    /** @param list<array> $tokens */
    private function __construct(private readonly array $tokens) {}

    /** @return array{table: string, ops: list<array>} */
    public static function parse(string $query): array
    {
        return (new self(Lexer::tokenize($query)))->query();
    }

    // ---- token helpers --------------------------------------------------

    private function peek(int $offset = 0): array
    {
        return $this->tokens[$this->i + $offset] ?? end($this->tokens);
    }

    private function next(): array
    {
        return $this->tokens[$this->i++];
    }

    private function isOp(string $v): bool
    {
        $t = $this->peek();

        return $t['t'] === 'op' && $t['v'] === $v;
    }

    private function isWord(string $v): bool
    {
        $t = $this->peek();

        return $t['t'] === 'ident' && strtolower($t['v']) === $v;
    }

    /** Consumes a comma if present; used to loop over comma-separated lists. */
    private function comma(): bool
    {
        if ($this->isOp(',')) {
            $this->next();

            return true;
        }

        return false;
    }

    private function expectOp(string $v): void
    {
        $t = $this->next();
        if ($t['t'] !== 'op' || $t['v'] !== $v) {
            throw new KqlError("Expected '{$v}'", $t['p']);
        }
    }

    private function expectWord(string $v): void
    {
        $t = $this->next();
        if ($t['t'] !== 'ident' || strtolower($t['v']) !== $v) {
            throw new KqlError("Expected '{$v}'", $t['p']);
        }
    }

    private function ident(): array
    {
        $t = $this->next();
        if ($t['t'] !== 'ident') {
            throw new KqlError('Expected a column or table name', $t['p']);
        }

        return $t;
    }

    private function int(): int
    {
        $t = $this->next();
        if ($t['t'] !== 'number' || ! is_int($t['v']) || $t['v'] < 0) {
            throw new KqlError('Expected a whole number', $t['p']);
        }

        return $t['v'];
    }

    /** asc / desc after a sort expression. KQL sorts descending by default. */
    private function direction(): bool
    {
        if ($this->isWord('asc')) {
            $this->next();

            return false;
        }
        if ($this->isWord('desc')) {
            $this->next();
        }

        return true;
    }

    // ---- statements -----------------------------------------------------

    private function query(): array
    {
        $table = $this->ident()['v'];
        $ops = [];
        while ($this->isOp('|')) {
            $this->next();
            $ops[] = $this->operator();
        }
        $t = $this->peek();
        if ($t['t'] !== 'eof') {
            throw new KqlError("Unexpected input. Did you forget a '|'?", $t['p']);
        }

        return ['table' => $table, 'ops' => $ops];
    }

    private function operator(): array
    {
        $t = $this->next();
        if ($t['t'] !== 'ident') {
            throw new KqlError("Expected an operator after '|'", $t['p']);
        }
        $word = strtolower($t['v']);

        switch ($word) {
            case 'where':
                return ['k' => 'where', 'e' => $this->expr()];
            case 'project':
            case 'extend':
                return ['k' => $word, 'items' => $this->assignList($word === 'extend')];
            case 'summarize':
                $aggs = [];
                if (! $this->isWord('by')) {
                    do {
                        $aggs[] = $this->agg();
                    } while ($this->comma());
                }
                $by = [];
                if ($this->isWord('by')) {
                    $this->next();
                    $by = $this->assignList(false);
                }
                if (! $aggs && ! $by) {
                    throw new KqlError("summarize needs an aggregation or a 'by' clause", $t['p']);
                }

                return ['k' => 'summarize', 'aggs' => $aggs, 'by' => $by];
            case 'order':
            case 'sort':
                $this->expectWord('by');
                $items = [];
                do {
                    $e = $this->expr();
                    $items[] = ['e' => $e, 'desc' => $this->direction()];
                } while ($this->comma());

                return ['k' => 'order', 'items' => $items];
            case 'take':
            case 'limit':
                return ['k' => 'take', 'n' => $this->int()];
            case 'top':
                $n = $this->int();
                $this->expectWord('by');
                $e = $this->expr();

                return ['k' => 'top', 'n' => $n, 'e' => $e, 'desc' => $this->direction()];
            case 'distinct':
                $cols = [];
                do {
                    $cols[] = $this->ident()['v'];
                } while ($this->comma());

                return ['k' => 'distinct', 'cols' => $cols];
            case 'count':
                return ['k' => 'count'];
        }

        if (in_array($word, self::UNSUPPORTED, true)) {
            throw new KqlError("'{$t['v']}' is not supported in this lab environment", $t['p']);
        }
        throw new KqlError("Unknown operator '{$t['v']}'", $t['p']);
    }

    /** Comma list of `Name = expr` or plain `expr` (named after its column). */
    private function assignList(bool $requireAlias): array
    {
        $items = [];
        do {
            $t = $this->peek();
            $after = $this->peek(1);
            if ($t['t'] === 'ident' && $after['t'] === 'op' && $after['v'] === '=') {
                $this->next();
                $this->next();
                $items[] = ['alias' => $t['v'], 'e' => $this->expr()];
            } else {
                if ($requireAlias) {
                    throw new KqlError('extend requires the form Name = expression', $t['p']);
                }
                $e = $this->expr();
                $items[] = ['alias' => self::nameOf($e), 'e' => $e];
            }
        } while ($this->comma());

        return $items;
    }

    private function agg(): array
    {
        $alias = null;
        $t = $this->peek();
        $after = $this->peek(1);
        if ($t['t'] === 'ident' && $after['t'] === 'op' && $after['v'] === '=') {
            $alias = $t['v'];
            $this->next();
            $this->next();
        }
        $fnTok = $this->ident();
        $fn = strtolower($fnTok['v']);
        if (! in_array($fn, self::AGGREGATES, true)) {
            throw new KqlError("Unsupported aggregation '{$fnTok['v']}'", $fnTok['p']);
        }
        $this->expectOp('(');
        $arg = $this->isOp(')') ? null : $this->expr();
        $this->expectOp(')');
        if ($fn !== 'count' && $arg === null) {
            throw new KqlError("{$fn}() needs a column", $fnTok['p']);
        }

        return ['alias' => $alias, 'fn' => $fn, 'arg' => $arg, 'p' => $fnTok['p']];
    }

    // ---- expressions ----------------------------------------------------

    private function expr(): array
    {
        return $this->orExpr();
    }

    private function orExpr(): array
    {
        $l = $this->andExpr();
        while ($this->isWord('or')) {
            $this->next();
            $l = ['k' => 'bin', 'op' => 'or', 'l' => $l, 'r' => $this->andExpr()];
        }

        return $l;
    }

    private function andExpr(): array
    {
        $l = $this->cmp();
        while ($this->isWord('and')) {
            $this->next();
            $l = ['k' => 'bin', 'op' => 'and', 'l' => $l, 'r' => $this->cmp()];
        }

        return $l;
    }

    private function cmp(): array
    {
        $l = $this->add();
        $t = $this->peek();
        $op = $t['t'] === 'op' ? $t['v'] : ($t['t'] === 'ident' ? strtolower($t['v']) : '');

        if (in_array($op, ['in', '!in', 'in~', '!in~'], true)) {
            $this->next();
            $this->expectOp('(');
            $list = [];
            if (! $this->isOp(')')) {
                do {
                    $list[] = $this->add();
                } while ($this->comma());
            }
            $this->expectOp(')');

            return ['k' => 'in', 'neg' => str_starts_with($op, '!'), 'ci' => str_ends_with($op, '~'), 'l' => $l, 'list' => $list];
        }
        if (in_array($op, self::COMPARISON, true)) {
            $this->next();

            return ['k' => 'bin', 'op' => $op, 'l' => $l, 'r' => $this->add()];
        }

        return $l;
    }

    private function add(): array
    {
        $l = $this->primary();
        while ($this->isOp('+') || $this->isOp('-') || $this->isOp('*') || $this->isOp('/')) {
            $op = $this->next()['v'];
            $l = ['k' => 'bin', 'op' => $op, 'l' => $l, 'r' => $this->primary()];
        }

        return $l;
    }

    private function primary(): array
    {
        $t = $this->next();
        switch ($t['t']) {
            case 'string':
            case 'number':
                return ['k' => 'lit', 'v' => $t['v']];
            case 'timespan':
                return ['k' => 'span', 'v' => $t['v']];
            case 'op':
                if ($t['v'] === '(') {
                    $e = $this->expr();
                    $this->expectOp(')');

                    return $e;
                }
                if ($t['v'] === '-') {
                    $n = $this->next();
                    if ($n['t'] === 'number') {
                        return ['k' => 'lit', 'v' => -$n['v']];
                    }
                }
                throw new KqlError("Unexpected '{$t['v']}'", $t['p']);
            case 'ident':
                $lower = strtolower($t['v']);
                if ($lower === 'true' || $lower === 'false') {
                    return ['k' => 'lit', 'v' => $lower === 'true'];
                }
                if ($lower === 'not' && $this->isOp('(')) {
                    $this->next();
                    $e = $this->expr();
                    $this->expectOp(')');

                    return ['k' => 'not', 'e' => $e];
                }
                if ($this->isOp('(')) {
                    $this->next();
                    $args = [];
                    if (! $this->isOp(')')) {
                        do {
                            $args[] = $this->expr();
                        } while ($this->comma());
                    }
                    $this->expectOp(')');

                    return ['k' => 'call', 'name' => $lower, 'args' => $args, 'p' => $t['p']];
                }

                return ['k' => 'col', 'name' => $t['v'], 'p' => $t['p']];
        }
        throw new KqlError('Unexpected end of query', $t['p']);
    }

    /** Column name for an unaliased expression: the column itself, or the column inside bin(). */
    public static function nameOf(array $e): string
    {
        if ($e['k'] === 'col') {
            return $e['name'];
        }
        if ($e['k'] === 'call' && $e['name'] === 'bin' && ($e['args'][0]['k'] ?? null) === 'col') {
            return $e['args'][0]['name'];
        }

        return 'Column1';
    }
}
