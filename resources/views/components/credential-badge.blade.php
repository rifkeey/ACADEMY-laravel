{{-- Shared badge visual: each programme's identity comes from its hue and icon. --}}
@props(['name', 'hue', 'icon', 'size' => 112, 'awarded' => true])
@php
    $icons = ['radar' => 'radar', 'crosshair' => 'crosshair', 'scale' => 'scale', 'network' => 'network', 'cloud' => 'cloud', 'shield-check' => 'shield-check'];
    $c1 = "hsl({$hue} 70% 55%)";
    $c2 = 'hsl('.(($hue + 40) % 360).' 65% 32%)';
    $gid = 'badge-'.$hue.'-'.\Illuminate\Support\Str::random(5);
@endphp
<div class="relative inline-flex shrink-0 items-center justify-center" style="width: {{ $size }}px; height: {{ $size }}px; opacity: {{ $awarded ? 1 : 0.45 }}" role="img" aria-label="{{ $awarded ? __(':name badge', ['name' => $name]) : __(':name badge (not yet earned)', ['name' => $name]) }}">
    <svg viewBox="0 0 120 120" width="{{ $size }}" height="{{ $size }}" aria-hidden="true">
        <defs><linearGradient id="{{ $gid }}" x1="0" y1="0" x2="1" y2="1"><stop offset="0" stop-color="{{ $c1 }}"/><stop offset="1" stop-color="{{ $c2 }}"/></linearGradient></defs>
        <path d="M60 4 L108 30 L108 90 L60 116 L12 90 L12 30 Z" fill="url(#{{ $gid }})"/>
        <path d="M60 14 L99 35 L99 85 L60 106 L21 85 L21 35 Z" fill="none" stroke="rgba(255,255,255,0.35)" stroke-width="1.5"/>
    </svg>
    <span class="absolute text-white" style="width: {{ $size * 0.36 }}px; height: {{ $size * 0.36 }}px;">
        <x-icon :name="$icons[$icon] ?? 'shield-check'" class="size-full" />
    </span>
</div>
