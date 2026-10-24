{{-- Examination result. Explanations are shown only after submission and only if the exam allows it. --}}
<x-layouts.site :title="__('Examination result')" :noindex="true">
    <x-ui.container class="py-12">
        <nav aria-label="{{ __('Breadcrumb') }}" class="mb-4 text-sm text-muted">
            <a href="{{ route('learn.course', $slug) }}" class="hover:text-text">{{ __('Course') }}</a> <span aria-hidden="true">/</span>
            <a href="{{ route('exam.show', $slug) }}" class="hover:text-text">{{ __('Final examination') }}</a> <span aria-hidden="true">/</span> <span class="text-text">{{ __('Attempt :n', ['n' => $attempt->attempt_number]) }}</span>
        </nav>

        @if ($attempt->status !== 'submitted')
            <x-ui.alert tone="warning" :title="__('This attempt has not been submitted')">
                <a href="{{ route('exam.take', [$slug, $attempt]) }}" class="font-semibold text-text underline">{{ __('Return to the attempt') }}</a>
            </x-ui.alert>
        @else
            <div class="rounded-2xl border p-8 {{ $attempt->passed ? 'border-success/40 bg-success/10' : 'border-danger/40 bg-danger/10' }}">
                <p class="text-sm font-semibold uppercase tracking-[0.14em] {{ $attempt->passed ? 'text-success' : 'text-danger' }}">{{ $attempt->passed ? __('Passed') : __('Not passed') }}</p>
                <p class="mt-2 text-4xl font-semibold tabular-nums">{{ $percent }}%</p>
                <p class="mt-2 text-muted">{{ __(':correct of :total correct. The pass mark for this attempt was :pass%.', ['correct' => $attempt->correct_count, 'total' => $attempt->total_count, 'pass' => $attempt->pass_percent]) }}</p>
                <p class="mt-1 text-sm text-subtle">{{ __('Submitted :date UTC', ['date' => $attempt->submitted_at->translatedFormat('j M Y, H:i')]) }}</p>
                <div class="mt-5 flex flex-wrap gap-3">
                    @if ($attempt->passed)
                        <x-ui.button :href="route('learn.course', $slug)">{{ __('View your course and certificate') }}</x-ui.button>
                    @else
                        <x-ui.button :href="route('exam.show', $slug)" variant="secondary">{{ __('Examination details and retakes') }}</x-ui.button>
                    @endif
                </div>
            </div>

            @if ($categories)
                <section aria-labelledby="cat-h" class="mt-10">
                    <h2 id="cat-h" class="text-lg font-semibold">{{ __('Performance by topic') }}</h2>
                    <div class="mt-4 grid gap-4 sm:grid-cols-2">
                        @foreach ($categories as $name => $c)
                            <x-ui.card class="p-4"><x-ui.progress :label="$name.' ('.$c['correct'].'/'.$c['total'].')'" :value="$c['total'] ? $c['correct'] / $c['total'] * 100 : 0" /></x-ui.card>
                        @endforeach
                    </div>
                </section>
            @endif

            @if ($items)
                <section aria-labelledby="rev-h" class="mt-10">
                    <h2 id="rev-h" class="text-lg font-semibold">{{ __('Question review') }}</h2>
                    <ol class="mt-4 space-y-4">
                        @foreach ($items as $item)
                            <li class="rounded-2xl border bg-surface p-5 {{ $item['isCorrect'] ? 'border-success/40' : 'border-danger/40' }}">
                                <div class="flex items-start justify-between gap-3">
                                    <p class="text-xs font-semibold uppercase tracking-wide text-subtle">{{ __('Question :n', ['n' => $item['position']]) }} · {{ $item['category'] }}</p>
                                    @if ($item['isCorrect'])<x-ui.pill tone="success">{{ __('Correct') }}</x-ui.pill>@else<x-ui.pill tone="danger">{{ __('Incorrect') }}</x-ui.pill>@endif
                                </div>
                                <x-markdown class="mt-2" :compact="true" :source="$item['prompt']" />
                                <ul class="mt-3 space-y-1.5 text-sm">
                                    @foreach ($item['options'] as $o)
                                        @php $picked = in_array($o['id'], $item['selected'], true); @endphp
                                        <li class="flex items-start gap-2 rounded-lg border px-3 py-2 {{ $o['isCorrect'] ? 'border-success/40 bg-success/5' : ($picked ? 'border-danger/40 bg-danger/5' : 'border-border') }}">
                                            <x-icon :name="$o['isCorrect'] ? 'check-circle-2' : ($picked ? 'x-circle' : 'circle')" class="mt-0.5 size-4 shrink-0 {{ $o['isCorrect'] ? 'text-success' : ($picked ? 'text-danger' : 'text-subtle') }}" />
                                            <span>{{ $o['text'] }}@if ($picked)<span class="text-subtle"> {{ __('(your answer)') }}</span>@endif</span>
                                        </li>
                                    @endforeach
                                </ul>
                                @if (! empty($item['explanation']))<p class="mt-3 text-sm text-muted"><span class="font-semibold text-text">{{ __('Explanation:') }}</span> {{ $item['explanation'] }}</p>@endif
                            </li>
                        @endforeach
                    </ol>
                </section>
            @endif
        @endif
    </x-ui.container>
</x-layouts.site>
