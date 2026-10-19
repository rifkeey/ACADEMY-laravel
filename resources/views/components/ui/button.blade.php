{{-- Button or link styled as a button. <x-ui.button href="..." variant="secondary" size="sm"> --}}
@props(['href' => null, 'variant' => 'primary', 'size' => 'md', 'type' => 'submit'])
@php
    $base = 'inline-flex items-center justify-center gap-2 rounded-lg font-semibold transition-colors disabled:cursor-not-allowed disabled:opacity-60';
    $variants = [
        'primary' => 'bg-accent text-accent-ink hover:bg-accent-strong',
        'secondary' => 'border border-border-strong bg-elevated text-text hover:bg-elevated-2',
        'ghost' => 'text-muted hover:bg-elevated hover:text-text',
        'danger' => 'border border-danger/50 bg-danger/10 text-danger hover:bg-danger/20',
    ];
    $sizes = ['sm' => 'h-8 px-3 text-sm', 'md' => 'h-10 px-4 text-sm', 'lg' => 'h-12 px-6 text-base'];
    $classes = "{$base} {$variants[$variant]} {$sizes[$size]}";
@endphp
@if ($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $classes]) }}>{{ $slot }}</a>
@else
    <button type="{{ $type }}" {{ $attributes->merge(['class' => $classes]) }}>{{ $slot }}</button>
@endif
