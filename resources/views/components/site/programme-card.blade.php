{{-- Catalogue card for a programme (whole card is clickable via the stretched title link). --}}
@props(['p', 'showOutcomes' => true])
@php
    $categories = ['soc' => __('Security operations'), 'offensive' => __('Offensive security'), 'grc' => __('Governance and risk'), 'network' => __('Network security'), 'cloud' => __('Cloud security')];
    $complete = $p->content_status === 'complete';
@endphp
<article class="group relative flex h-full flex-col rounded-2xl border border-border bg-surface p-6 transition-colors hover:border-border-strong">
    <div class="flex flex-wrap items-center gap-2">
        <x-ui.pill tone="accent">{{ $categories[$p->category] ?? $p->category }}</x-ui.pill>
        <x-ui.pill>{{ $p->level }}</x-ui.pill>
        @unless ($complete)<x-ui.pill tone="warning">{{ __('Curriculum in development') }}</x-ui.pill>@endunless
    </div>
    <h3 class="mt-4 text-xl font-semibold">
        <a href="{{ lroute('programmes.show', $p) }}" class="after:absolute after:inset-0 focus-visible:outline-none"><span class="relative">{{ $p->title }}</span></a>
    </h3>
    <p class="mt-2 text-sm leading-relaxed text-muted">{{ $p->summary }}</p>
    <p class="mt-3 text-xs text-subtle"><span class="font-semibold text-muted">{{ __('For:') }}</span> {{ implode(' · ', array_slice($p->audience, 0, 3)) }}</p>
    @if ($showOutcomes && $complete)
        <ul class="mt-4 space-y-1.5 text-sm text-muted">
            @foreach (array_slice($p->outcomes, 0, 3) as $o)
                <li class="flex gap-2"><span aria-hidden="true" class="mt-2 size-1.5 shrink-0 rounded-full bg-accent"></span>{{ $o }}</li>
            @endforeach
        </ul>
    @endif
    <dl class="mt-5 grid grid-cols-3 gap-2 border-t border-border pt-4 text-xs text-muted">
        <div class="flex items-center gap-1.5"><x-icon name="layers" class="size-3.5" /><dt class="sr-only">{{ __('Modules') }}</dt><dd>{{ __(':count modules', ['count' => $p->currentVersion?->modules_count ?? 0]) }}</dd></div>
        <div class="flex items-center gap-1.5"><x-icon name="flask-conical" class="size-3.5" /><dt class="sr-only">{{ __('Labs') }}</dt><dd>{{ __($complete ? ':count labs' : ':count labs so far', ['count' => $p->currentVersion?->labs_count ?? 0]) }}</dd></div>
        <div class="flex items-center gap-1.5"><x-icon name="clock" class="size-3.5" /><dt class="sr-only">{{ __('Estimated study time') }}</dt><dd>{{ __('~:hours h (estimate)', ['hours' => $p->estimated_hours]) }}</dd></div>
    </dl>
    <p class="mt-2 text-xs text-subtle">{{ __('Includes: :list', ['list' => implode('; ', array_slice($p->inclusions, 0, 2))]) }}</p>
    <div class="mt-auto flex items-center justify-between pt-5">
        @if ($p->price)
            <p class="text-lg font-semibold tabular-nums">{{ \App\Support\Money::format($p->price->amount_minor, $p->price->currency) }}</p>
        @else
            <p class="text-sm font-semibold text-muted">{{ ($p->prices ?? collect())->isNotEmpty() ? __('Price in :currency coming soon', ['currency' => \App\Support\Currency::short(\App\Support\Currency::current())]) : __('Price on request') }}</p>
        @endif
        <span class="relative inline-flex items-center gap-1 text-sm font-semibold text-accent">{{ $p->enrolment_open ? __('View and enrol') : __('View programme') }} <x-icon name="arrow-right" class="size-4 transition-transform group-hover:translate-x-0.5" /></span>
    </div>
</article>
