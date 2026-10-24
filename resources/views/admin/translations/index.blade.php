<x-layouts.admin :title="__('French translations')" :description="__('Course content in French. Learners see an item in French only once its translation is approved and still matches the English; until then they see the English with a notice.')">
    @php
        $states = [
            'approved' => [__('Approved'), 'success'],
            'in_review' => [__('In review'), 'blue'],
            'draft' => [__('Draft'), 'warning'],
            'outdated' => [__('Out of date'), 'danger'],
            'missing' => [__('Not started'), 'neutral'],
        ];
        $types = ['programme' => __('Programme'), 'module' => __('Module'), 'lesson' => __('Lesson'), 'lab' => __('Lab'), 'question' => __('Question'), 'exam' => __('Examination'), 'badge' => __('Badge'), 'article' => __('Article'), 'legal' => __('Legal page')];
        $tab = $site ? 'site' : $programme?->slug;
    @endphp

    <nav aria-label="{{ __('Programmes') }}" class="flex flex-wrap gap-2">
        @foreach ($programmes as $p)
            <a href="{{ route('admin.translations.index', ['programme' => $p->slug]) }}" @class(['rounded-lg border px-3 py-1.5 text-sm', 'border-accent bg-accent/10 font-semibold text-text' => $programme?->id === $p->id, 'border-border text-muted hover:text-text' => $programme?->id !== $p->id]) @if ($programme?->id === $p->id) aria-current="page" @endif>{{ $p->title }}</a>
        @endforeach
        <a href="{{ route('admin.translations.index', ['programme' => 'site']) }}" @class(['rounded-lg border px-3 py-1.5 text-sm', 'border-accent bg-accent/10 font-semibold text-text' => $site, 'border-border text-muted hover:text-text' => ! $site]) @if ($site) aria-current="page" @endif>{{ __('Site pages') }}</a>
    </nav>
    @if ($reviewQueue > 0)
        <p class="mt-3 text-sm text-muted">{{ trans_choice(':count translation is waiting for review across all programmes.|:count translations are waiting for review across all programmes.', $reviewQueue, ['count' => $reviewQueue]) }}</p>
    @endif

    @if ($tab)
        <section aria-labelledby="sum-h" class="mt-6">
            <h2 id="sum-h" class="sr-only">{{ __('Summary') }}</h2>
            <div class="grid gap-3 sm:grid-cols-3 lg:grid-cols-5">
                @foreach ($states as $key => [$label, $tone])
                    <a href="{{ route('admin.translations.index', ['programme' => $tab, 'state' => $state === $key ? null : $key]) }}" @class(['rounded-xl border bg-surface p-4 hover:border-accent', 'border-accent' => $state === $key, 'border-border' => $state !== $key])>
                        <p class="text-xs text-muted">{{ $label }}</p>
                        <p class="mt-1 text-2xl font-semibold tabular-nums">{{ $counts[$key] }}<span class="text-sm font-normal text-subtle"> / {{ $total }}</span></p>
                    </a>
                @endforeach
            </div>
            <div class="mt-3"><x-ui.progress :value="$total ? intdiv($counts['approved'] * 100, $total) : 0" :label="__('Approved in French')" /></div>
            @if ($total > 0 && $counts['approved'] === $total)
                <p class="mt-2 text-sm text-success">{{ $site ? __('Every site page is approved in French.') : __('Every part of this programme is approved in French.') }}</p>
            @endif
        </section>

        <section aria-labelledby="items-h" class="mt-8">
            <h2 id="items-h" class="text-lg font-semibold">{{ $state ? $states[$state][0] : __('All items') }}</h2>
            <div class="mt-3">
                <x-ui.table :head="[__('Type'), __('Item'), __('Status'), __('Last change'), '']" :caption="__('Translatable items')">
                    @forelse ($items as $row)
                        <tr>
                            <x-ui.td class="whitespace-nowrap text-muted">{{ $types[$row['type']] }}</x-ui.td>
                            <x-ui.td lang="en">{{ $row['label'] }}</x-ui.td>
                            <x-ui.td><x-ui.pill :tone="$states[$row['state']][1]">{{ $states[$row['state']][0] }}</x-ui.pill></x-ui.td>
                            <x-ui.td class="whitespace-nowrap text-muted">{{ $row['translation']?->updated_at?->translatedFormat('j M Y') ?? '—' }}</x-ui.td>
                            <x-ui.td><a href="{{ route('admin.translations.edit', ['type' => $row['type'], 'id' => $row['item']->getKey(), 'programme' => $tab]) }}" class="text-sm font-semibold text-accent hover:underline">{{ $row['state'] === 'missing' ? __('Translate') : __('Edit') }}</a></x-ui.td>
                        </tr>
                    @empty
                        <tr><x-ui.td colspan="5" class="text-center text-muted">{{ __('Nothing in this list.') }}</x-ui.td></tr>
                    @endforelse
                </x-ui.table>
            </div>
        </section>
    @endif
</x-layouts.admin>
