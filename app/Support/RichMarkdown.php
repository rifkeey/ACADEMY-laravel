<?php

namespace App\Support;

use Illuminate\Support\Str;

/**
 * Markdown with a few teaching blocks, rendered safely.
 *
 *   :::note Optional title        (also: tip, warning, scenario, takeaway)
 *   Markdown text...
 *   :::
 *
 *   :::reveal Show the worked solution
 *   Hidden until the learner opens it (a native <details> element).
 *   :::
 *
 * Every piece of text, including the inner Markdown and block titles, goes through the same
 * escaping as ordinary lessons: raw HTML is escaped and unsafe links are removed. The only HTML
 * added is the fixed wrapper markup below. Blocks do not nest; ":::" lines inside fenced code
 * blocks are left alone.
 */
final class RichMarkdown
{
    public const BLOCKS = ['note', 'tip', 'warning', 'scenario', 'takeaway', 'reveal'];

    private const LABELS = ['note' => 'Note', 'tip' => 'Tip', 'warning' => 'Warning', 'scenario' => 'Scenario', 'takeaway' => 'Key takeaway'];

    public static function render(string $source): string
    {
        $html = '';
        $buffer = [];
        $block = null;          // [type, title, lines]
        $fence = null;          // the fence marker while inside a fenced code block

        foreach (preg_split('/\R/', $source) as $line) {
            if (preg_match('/^\s*(```|~~~)/', $line, $m)) {
                $fence = $fence === null ? $m[1] : ($fence === $m[1] ? null : $fence);
            }
            if ($fence === null && $block === null && preg_match('/^:::(\w+)\s*(.*)$/', trim($line), $m) && in_array($m[1], self::BLOCKS, true)) {
                $html .= self::markdown(implode("\n", $buffer));
                $buffer = [];
                $block = [$m[1], trim($m[2]), []];

                continue;
            }
            if ($fence === null && $block !== null && trim($line) === ':::') {
                $html .= self::wrap(...$block);
                $block = null;

                continue;
            }
            if ($block !== null) {
                $block[2][] = $line;
            } else {
                $buffer[] = $line;
            }
        }
        if ($block !== null) {          // unclosed block: render it anyway rather than lose the text
            $html .= self::wrap(...$block);
        }

        return $html.self::markdown(implode("\n", $buffer));
    }

    private static function markdown(string $text): string
    {
        return trim($text) === '' ? '' : Str::markdown($text, ['html_input' => 'escape', 'allow_unsafe_links' => false]);
    }

    private static function wrap(string $type, string $title, array $lines): string
    {
        $inner = self::markdown(implode("\n", $lines));
        if ($type === 'reveal') {
            return '<details class="md-reveal"><summary>'.e($title !== '' ? $title : 'Show answer').'</summary><div class="md-reveal-body">'.$inner.'</div></details>';
        }
        $label = e($title !== '' ? $title : self::LABELS[$type]);

        return '<aside class="md-callout md-callout-'.$type.'" role="note"><p class="md-callout-title">'.$label.'</p>'.$inner.'</aside>';
    }
}
