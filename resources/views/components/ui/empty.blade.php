@props(['title', 'icon' => null])
<div class="flex flex-col items-center rounded-2xl border border-dashed border-border-strong px-6 py-12 text-center">
    @if ($icon)<x-icon :name="$icon" class="mb-3 size-8 text-subtle" />@endif
    <p class="font-semibold">{{ $title }}</p>
    @if (trim($slot) !== '')<div class="mt-1 max-w-md text-sm text-muted">{{ $slot }}</div>@endif
    @isset($action)<div class="mt-5">{{ $action }}</div>@endisset
</div>
