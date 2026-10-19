@props(['label', 'value', 'hint' => null])
<x-ui.card class="p-5">
    <p class="text-sm text-muted">{{ $label }}</p>
    <p class="mt-1 text-2xl font-semibold tabular-nums">{{ $value }}</p>
    @if ($hint)<p class="mt-1 text-xs text-subtle">{{ $hint }}</p>@endif
</x-ui.card>
