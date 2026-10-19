@props(['tone' => 'neutral'])
@php
    $tones = [
        'neutral' => 'border-border-strong bg-elevated text-muted',
        'accent' => 'border-accent/40 bg-accent/10 text-accent-strong',
        'success' => 'border-success/40 bg-success/10 text-success',
        'warning' => 'border-warning/40 bg-warning/10 text-warning',
        'danger' => 'border-danger/40 bg-danger/10 text-danger',
        'blue' => 'border-blue/40 bg-blue/10 text-blue',
    ];
@endphp
<span {{ $attributes->merge(['class' => 'inline-flex items-center gap-1 rounded-full border px-2.5 py-0.5 text-xs font-medium '.$tones[$tone]]) }}>{{ $slot }}</span>
