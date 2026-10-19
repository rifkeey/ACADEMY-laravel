{{-- Brand lockup: the amaniq wordmark, a thin divider and a separate division name. --}}
@props(['division' => 'Academy', 'size' => 'md'])
@php
    $s = [
        'sm' => ['h-5 w-auto', 'text-sm', 'gap-2.5', 'h-4'],
        'md' => ['h-7 w-auto', 'text-base', 'gap-3', 'h-5'],
        'lg' => ['h-12 w-auto', 'text-2xl', 'gap-4', 'h-9'],
    ][$size];
@endphp
<span {{ $attributes->merge(['class' => "inline-flex items-center {$s[2]}"]) }} role="img" aria-label="amaniq {{ $division }}">
    <x-wordmark :class="$s[0].' shrink-0'" title="" />
    <span aria-hidden="true" class="{{ $s[3] }} w-px bg-current opacity-40"></span>
    <span aria-hidden="true" class="{{ $s[1] }} font-semibold uppercase leading-none tracking-[0.18em]">{{ $division }}</span>
</span>
