<x-layouts.site :title="__('Cybersecurity programmes')" :description="__('Compare AMANIQ Academy specializations: SOC Analyst, Penetration Testing, GRC, Network Security and Cloud Security. Outcomes, labs, study time and prices.')">
    <x-ui.container class="py-14">
        <x-ui.page-header :eyebrow="__('Specializations')" :title="__('Cybersecurity programmes')" :description="__('Every programme combines original lessons, hands-on simulated labs and a 40-question final examination. Study times are estimates.')" />

        <form method="GET" role="search" class="mt-8 grid gap-3 rounded-2xl border border-border bg-surface p-4 sm:grid-cols-[1fr_auto_auto_auto]">
            <label for="q" class="sr-only">{{ __('Search programmes') }}</label>
            <div class="relative">
                <x-icon name="search" class="pointer-events-none absolute left-3 top-1/2 size-4 -translate-y-1/2 text-subtle" />
                <x-ui.input name="q" :value="$q" :placeholder="__('Search by skill, role or topic')" class="pl-9" />
            </div>
            <label for="category" class="sr-only">{{ __('Category') }}</label>
            <x-ui.select name="category">
                <option value="">{{ __('All categories') }}</option>
                @foreach ($categories as $k => $v)<option value="{{ $k }}" @selected($category === $k)>{{ __($v) }}</option>@endforeach
            </x-ui.select>
            <label for="sort" class="sr-only">{{ __('Sort by') }}</label>
            <x-ui.select name="sort">
                @foreach ($sorts as $k => $v)<option value="{{ $k }}" @selected($sort === $k)>{{ __($v) }}</option>@endforeach
            </x-ui.select>
            <x-ui.button variant="secondary">{{ __('Apply') }}</x-ui.button>
        </form>

        <div class="mt-4 flex justify-end"><x-site.currency-switch /></div>
        <p class="mt-2 text-sm text-muted" aria-live="polite">{{ trans_choice(':count programme|:count programmes', $programmes->count()) }}@if ($q !== '') {{ __('matching “:q”', ['q' => $q]) }}@endif</p>

        @if ($programmes->isEmpty())
            <div class="mt-6">
                <x-ui.empty :title="__('No programmes match your search')">
                    {{ __('Try a broader term such as “cloud”, “SOC” or “risk”.') }}
                    <x-slot:action><a href="{{ lroute('programmes.index') }}" class="text-sm font-semibold text-accent">{{ __('Clear filters') }}</a></x-slot:action>
                </x-ui.empty>
            </div>
        @else
            <div class="mt-6 grid gap-5 md:grid-cols-2 lg:grid-cols-3">
                @foreach ($programmes as $p)<x-site.programme-card :p="$p" />@endforeach
            </div>
        @endif
    </x-ui.container>
</x-layouts.site>
