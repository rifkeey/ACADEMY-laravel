{{-- Status message. Danger alerts use role="alert" so screen readers announce them. --}}
@props(['tone' => 'info', 'title' => null])
@php
    $tones = [
        'info' => ['border-blue/40 bg-blue/10', 'text-blue', 'info'],
        'success' => ['border-success/40 bg-success/10', 'text-success', 'check-circle-2'],
        'warning' => ['border-warning/40 bg-warning/10', 'text-warning', 'triangle-alert'],
        'danger' => ['border-danger/40 bg-danger/10', 'text-danger', 'x-circle'],
    ];
    [$box, $iconColour, $icon] = $tones[$tone];
@endphp
<div role="{{ $tone === 'danger' ? 'alert' : 'status' }}" {{ $attributes->merge(['class' => "flex gap-3 rounded-xl border p-4 text-sm text-text {$box}"]) }}>
    <x-icon :name="$icon" class="mt-0.5 size-4 shrink-0 {{ $iconColour }}" />
    <div class="min-w-0">
        @if ($title)<p class="font-semibold">{{ $title }}</p>@endif
        @if (trim($slot) !== '')<div class="text-muted {{ $title ? 'mt-1' : '' }}">{{ $slot }}</div>@endif
    </div>
</div>
