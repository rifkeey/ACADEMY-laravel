<?php

namespace Tests\Unit;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * WCAG 2.1 contrast checks for the design tokens in resources/css/app.css. Every text colour
 * must reach 4.5:1 (normal text, level AA) on every surface it is used on.
 */
class ColourContrastTest extends TestCase
{
    private function tokens(): array
    {
        preg_match_all('/--([a-z0-9-]+):\s*(#[0-9a-f]{6});/i', file_get_contents(__DIR__.'/../../resources/css/app.css'), $m, PREG_SET_ORDER);

        return array_column($m, 2, 1);
    }

    private function luminance(string $hex): float
    {
        $channel = function (int $v): float {
            $c = $v / 255;

            return $c <= 0.03928 ? $c / 12.92 : (($c + 0.055) / 1.055) ** 2.4;
        };
        [$r, $g, $b] = sscanf($hex, '#%02x%02x%02x');

        return 0.2126 * $channel($r) + 0.7152 * $channel($g) + 0.0722 * $channel($b);
    }

    private function ratio(string $a, string $b): float
    {
        [$hi, $lo] = [max($this->luminance($a), $this->luminance($b)), min($this->luminance($a), $this->luminance($b))];

        return ($hi + 0.05) / ($lo + 0.05);
    }

    #[Test]
    public function text_colours_meet_wcag_aa_on_every_surface(): void
    {
        $t = $this->tokens();
        foreach (['bg', 'surface', 'elevated'] as $surface) {
            foreach (['text', 'muted', 'subtle', 'accent', 'accent-strong', 'blue', 'success', 'warning', 'danger'] as $text) {
                $r = $this->ratio($t[$text], $t[$surface]);
                $this->assertGreaterThanOrEqual(4.5, $r, sprintf('%s on %s is %.2f:1', $text, $surface, $r));
            }
        }
        // Dark text on the accent button.
        $this->assertGreaterThanOrEqual(4.5, $this->ratio($t['accent-ink'], $t['accent']));
    }
}
