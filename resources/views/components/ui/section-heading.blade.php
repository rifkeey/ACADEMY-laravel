@props(['eyebrow' => null, 'title', 'description' => null, 'align' => 'left'])
<div class="mb-10 max-w-2xl {{ $align === 'center' ? 'mx-auto text-center' : '' }}">
    @if ($eyebrow)<p class="mb-2 text-sm font-semibold uppercase tracking-[0.14em] text-accent">{{ $eyebrow }}</p>@endif
    <h2 class="text-2xl font-semibold tracking-tight sm:text-3xl">{{ $title }}</h2>
    @if ($description)<p class="mt-3 text-base leading-relaxed text-muted sm:text-lg">{{ $description }}</p>@endif
</div>
