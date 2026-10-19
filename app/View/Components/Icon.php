<?php

namespace App\View\Components;

use Illuminate\View\Component;

/**
 * Inline Lucide icon from resources/icons (decorative unless a label is given).
 * Usage: <x-icon name="award" class="size-4" />  or  <x-icon name="lock" label="Locked" />
 */
class Icon extends Component
{
    /** @var array<string, string> parsed SVG bodies, cached per request */
    private static array $cache = [];

    public function __construct(public string $name, public string $class = 'size-4', public ?string $label = null) {}

    public function render(): string
    {
        $svg = self::$cache[$this->name] ??= self::load($this->name);
        $a11y = $this->label ? 'role="img" aria-label="'.e($this->label).'"' : 'aria-hidden="true" focusable="false"';
        // Keep the default size when the caller only adds colour or spacing classes.
        $class = preg_match('/(^|\s)(size|w|h)-/', $this->class) ? $this->class : 'size-4 '.$this->class;

        return str_replace('<svg', '<svg class="'.e(trim($class)).'" '.$a11y, $svg);
    }

    private static function load(string $name): string
    {
        $path = resource_path("icons/{$name}.svg");
        if (! preg_match('/^[a-z0-9-]+$/', $name) || ! is_file($path)) {
            return '';
        }
        $svg = file_get_contents($path);
        $svg = preg_replace('/<!--.*?-->/s', '', $svg);                       // licence comment
        $svg = preg_replace('/\s(class|width|height)="[^"]*"/', '', $svg);      // sized by CSS instead

        return trim(preg_replace('/\s+/', ' ', $svg));
    }
}
