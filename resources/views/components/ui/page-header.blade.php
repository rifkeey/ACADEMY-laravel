@props(['eyebrow' => null, 'title', 'description' => null])
<div class="flex flex-col gap-4 border-b border-border pb-8 sm:flex-row sm:items-end sm:justify-between">
    <div class="max-w-3xl">
        @if ($eyebrow)<p class="mb-2 text-sm font-semibold uppercase tracking-[0.14em] text-accent">{{ $eyebrow }}</p>@endif
        <h1 class="text-3xl font-semibold tracking-tight text-text sm:text-4xl">{{ $title }}</h1>
        @if ($description)<div class="mt-3 text-lg leading-relaxed text-muted">{{ $description }}</div>@endif
    </div>
    @isset($actions)<div class="flex shrink-0 flex-wrap gap-3">{{ $actions }}</div>@endisset
</div>
