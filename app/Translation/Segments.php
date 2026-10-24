<?php

namespace App\Translation;

/**
 * Finds the translatable prose in a content item and lays a translation over it.
 *
 * Every piece of prose has a path: a column name, then keys, list positions, or [id] for list
 * entries that carry an "id" (lab tasks and options), so reordering tasks keeps their text:
 *
 *   title · objectives.0 · knowledge_checks.2.options.1 · content.tasks[spray-ip].prompt
 *   content.tasks[pattern].input.options[spray].label · validator.tasks.spray-ip.feedback.correct
 *
 * Extraction and application use the same walk, so the paths always match. Only prose keys
 * are translated; everything else (ids, types, answer keys, KQL tables, IP addresses, starter
 * policies, code) is never touched, so a translation cannot change how a lab or quiz works.
 * In a lab workspace only reading documents count: titles, and bodies in markdown format.
 *
 * Two kinds of segment: "prose" replaces the English text; "append" (accepted answers and
 * required terms in written deliverables) adds French terms to the English list, never removes.
 */
final class Segments
{
    public const PROSE = 'prose';

    public const APPEND = 'append';

    /** Field rules: text, markdown and list are whole columns; the rest walk JSON. */
    private const RULES = [
        'faqs' => ['q', 'a'],
        'checks' => ['question', 'options', 'explanation', 'optionFeedback'],
        'lab_content' => ['competency', 'hints', 'instructions', 'label', 'objectives', 'placeholder', 'prompt', 'reflection', 'rubric', 'scenario', 'scope', 'simulationNotice', 'template', 'title'],
    ];

    /** Never walked: learner data and technical values stay identical in every language. */
    private const NEVER = ['workspace', 'id', 'type', 'kind', 'starter', 'table', 'tables', 'datasets', 'zones', 'code', 'query', 'unit'];

    /**
     * English segments of one field: path => text (append lists joined by new lines).
     *
     * @return array<string, array{text: string, kind: string}>
     */
    public static function extract(string $field, string $rule, mixed $value): array
    {
        $out = [];
        self::walk($value, $field, null, $rule, function (&$node, string $path, string $kind) use (&$out) {
            $text = $kind === self::APPEND ? implode("\n", array_map('strval', $node)) : (string) $node;
            if (trim($text) !== '') {
                $out[$path] = ['text' => $text, 'kind' => $kind];
            }
        });

        return $out;
    }

    /** Returns the field with translations laid over it. $texts: path => translated text. */
    public static function apply(string $field, string $rule, mixed $value, array $texts): mixed
    {
        self::walk($value, $field, null, $rule, function (&$node, string $path, string $kind) use ($texts) {
            $t = $texts[$path] ?? null;
            if (! is_string($t) || trim($t) === '') {
                return;
            }
            if ($kind === self::APPEND) {
                $extra = array_values(array_filter(array_map('trim', preg_split('/\R/u', $t)), fn ($s) => $s !== ''));
                $node = array_values(array_unique([...$node, ...$extra]));
            } else {
                $node = $t;
            }
        });

        return $value;
    }

    private static function walk(mixed &$node, string $path, ?string $key, string $rule, callable $visit): void
    {
        $kind = self::classify($rule, $key, $path, $node);
        if ($kind !== null) {
            $visit($node, $path, $kind);

            return;
        }
        if (! is_array($node)) {
            return;
        }
        $isList = array_is_list($node);
        foreach ($node as $k => &$child) {
            // In a lab workspace only the reading documents are prose: their titles, and the body of
            // markdown documents. Datasets, tables, queries and code documents stay as they are.
            if ($rule === 'lab_content' && $k === 'workspace' && ! $isList) {
                foreach ($child['documents'] ?? [] as $i => $doc) {
                    $base = "{$path}.workspace.documents.{$i}";
                    if (is_string($doc['title'] ?? null)) {
                        $visit($child['documents'][$i]['title'], "{$base}.title", self::PROSE);
                    }
                    if (($doc['format'] ?? null) === 'markdown' && is_string($doc['body'] ?? null)) {
                        $visit($child['documents'][$i]['body'], "{$base}.body", self::PROSE);
                    }
                }

                continue;
            }
            if (! $isList && in_array($k, self::NEVER, true)) {
                continue;
            }
            $childPath = match (true) {
                ! $isList => "{$path}.{$k}",
                is_array($child) && is_string($child['id'] ?? null) => "{$path}[{$child['id']}]",
                default => "{$path}.{$k}",
            };
            // A list's elements are classified by the list's own key (e.g. each of "hints").
            self::walk($child, $childPath, $isList ? $key : (string) $k, $rule, $visit);
        }
        unset($child);
    }

    private static function classify(string $rule, ?string $key, string $path, mixed $node): ?string
    {
        return match ($rule) {
            'text', 'markdown' => is_string($node) ? self::PROSE : null,
            'list' => is_string($node) && $key === null && str_contains($path, '.') ? self::PROSE : null,
            'lab_validator' => self::validator($path, $node),
            default => is_string($node) && $key !== null && in_array($key, self::RULES[$rule] ?? [], true) ? self::PROSE : null,
        };
    }

    /** Lab answer keys: only feedback, the debrief and deliverable labels are prose. */
    private static function validator(string $path, mixed $node): ?string
    {
        if (is_string($node) && preg_match('/^validator\.(tasks\.[^.]+\.feedback\.(correct|incorrect)|debrief|tasks\.[^.]+\.mustMention\.\d+\.label)$/', $path)) {
            return self::PROSE;
        }
        if (is_array($node) && array_is_list($node) && $node !== [] && array_filter($node, 'is_string') === $node
            && preg_match('/^validator\.tasks\.[^.]+\.(accept|mustMention\.\d+\.any)$/', $path)) {
            return self::APPEND;
        }

        return null;
    }
}
