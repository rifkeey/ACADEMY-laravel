<?php

namespace App\View\Components;

use Illuminate\Support\Str;
use Illuminate\View\Component;

/**
 * The "amaniq" parent-brand wordmark as inline SVG. Inherits the text colour (currentColor), so
 * it works on any background. Each use gets a unique mask id to keep the page's IDs valid.
 */
class Wordmark extends Component
{
    private static ?string $svg = null;

    public function __construct(public string $class = 'h-6 w-auto', public string $title = 'amaniq') {}

    public function render(): string
    {
        self::$svg ??= file_get_contents(resource_path('brand/amaniq-wordmark.svg'));
        $id = 'amq-'.Str::lower(Str::random(8));
        $svg = str_replace('__ID__', $id, self::$svg);
        $label = $this->title === '' ? 'aria-hidden="true"' : 'aria-label="'.e($this->title).'"';

        return preg_replace('/<svg ([^>]*)role="img" aria-label="amaniq"/', '<svg class="'.e($this->class).'" $1role="img" '.$label, $svg, 1);
    }
}
