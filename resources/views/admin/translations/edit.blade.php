@php
    $types = ['programme' => __('Programme'), 'module' => __('Module'), 'lesson' => __('Lesson'), 'lab' => __('Lab'), 'question' => __('Question'), 'exam' => __('Examination'), 'badge' => __('Badge'), 'article' => __('Article'), 'legal' => __('Legal page')];
    $fieldLabels = [
        'title' => __('Title'), 'summary' => __('Summary'), 'description' => __('Description'), 'audience' => __('Who it is for'),
        'prerequisites' => __('Prerequisites'), 'outcomes' => __('Outcomes'), 'inclusions' => __('What is included'), 'faqs' => __('Questions and answers'),
        'purpose' => __('Purpose'), 'prerequisite_knowledge' => __('Prerequisite knowledge'), 'objectives' => __('Objectives'), 'key_concepts' => __('Key concepts'),
        'assessment_criteria' => __('Assessment criteria'), 'body_md' => __('Text (Markdown)'), 'knowledge_checks' => __('Knowledge checks'),
        'content' => __('Lab content'), 'validator' => __('Feedback and marking terms'), 'prompt' => __('Question'), 'explanation' => __('Explanation'),
        'objective' => __('Objective'), 'options' => __('Answer options'), 'name' => __('Name'), 'criteria' => __('Criteria'),
    ];
    $states = ['approved' => [__('Approved'), 'success'], 'in_review' => [__('In review'), 'blue'], 'draft' => [__('Draft'), 'warning'], 'outdated' => [__('Out of date'), 'danger'], 'missing' => [__('Not started'), 'neutral']];
    $heading = $item->title ?? $item->name ?? $item->prompt ?? '';
    $segments = $translation?->segments ?? [];
    $stale = array_flip($compare['stale']);
    $groups = collect($english)->groupBy(fn ($s, $path) => preg_split('/[.\[]/', $path, 2)[0], preserveKeys: true);
    $user = auth()->user();
    $canReview = $user->can('translations.review') && $translation && $translation->translated_by !== $user->id;
    $enc = fn (string $path) => strtr(base64_encode($path), '+/', '-_');
@endphp
<x-layouts.admin :title="__('Translate: :item', ['item' => \Illuminate\Support\Str::limit($heading, 60)])" :description="__('Write the French beside each piece of English. Ids, answers, data and code are shared with the English and cannot change here.')">
    <p class="mb-4 text-sm"><a href="{{ route('admin.translations.index', array_filter(['programme' => $programme])) }}" class="text-muted hover:text-text">← {{ __('French translations') }}</a></p>

    <div class="flex flex-wrap items-center gap-3">
        <x-ui.pill>{{ $types[$type] }}</x-ui.pill>
        <x-ui.pill :tone="$states[$state][1]">{{ $states[$state][0] }}</x-ui.pill>
        @if ($translation)
            <span class="text-sm text-muted">
                {{ __('Last edited by :name on :date.', ['name' => $translation->translator?->name ?? __('import'), 'date' => $translation->updated_at->translatedFormat('j M Y H:i')]) }}
                @if ($translation->reviewed_at) {{ __('Reviewed by :name on :date.', ['name' => $translation->reviewer?->name ?? '—', 'date' => $translation->reviewed_at->translatedFormat('j M Y')]) }} @endif
            </span>
        @endif
    </div>
    @if ($translation?->origin === 'machine' && $translation->status !== 'approved')
        <x-ui.alert tone="warning" class="mt-4" :title="__('Machine-drafted')">{{ __('This draft was produced by a translation tool. Check every segment for meaning, terminology and tone before sending it for review.') }}</x-ui.alert>
    @endif
    @if ($translation?->review_note)
        <x-ui.alert tone="info" class="mt-4" :title="__('Reviewer\'s note')">{{ $translation->review_note }}</x-ui.alert>
    @endif
    @if ($compare['stale'])
        <x-ui.alert tone="danger" class="mt-4" :title="__('The English has changed')">{{ trans_choice(':count segment changed in English since it was translated. It is marked below with the previous English.|:count segments changed in English since they were translated. They are marked below with the previous English.', count($compare['stale']), ['count' => count($compare['stale'])]) }}</x-ui.alert>
    @endif

    <form method="POST" action="{{ route('admin.translations.update', ['type' => $type, 'id' => $item->getKey()]) }}" class="mt-6 space-y-8">
        @csrf @method('PUT')
        @foreach ($groups as $field => $segs)
            <fieldset class="rounded-2xl border border-border bg-surface p-5">
                <legend class="px-1 text-sm font-semibold">{{ $fieldLabels[$field] ?? $field }} <span class="font-normal text-subtle">({{ count($segs) }})</span></legend>
                <div class="space-y-6">
                    @foreach ($segs as $path => $seg)
                        @php
                            $mine = $segments[$path] ?? null;
                            $long = mb_strlen($seg['text']) > 140 || str_contains($seg['text'], "\n");
                            $id = 'seg-'.md5($path);
                        @endphp
                        <div @class(['grid gap-3 lg:grid-cols-2', 'rounded-xl border border-danger/40 p-3' => isset($stale[$path])])>
                            <div>
                                <p class="font-mono text-[11px] text-subtle">{{ $path }}</p>
                                <div lang="en" class="mt-1 whitespace-pre-wrap rounded-lg bg-elevated px-3 py-2 text-sm">{{ $seg['text'] }}</div>
                                @if (isset($stale[$path]))
                                    <p class="mt-2 text-xs font-semibold text-danger">{{ __('Previous English, which the French was written from:') }}</p>
                                    <div lang="en" class="mt-1 whitespace-pre-wrap rounded-lg bg-danger/5 px-3 py-2 text-xs text-muted">{{ $mine['source'] }}</div>
                                @endif
                                @if ($seg['kind'] === 'append')
                                    <p class="mt-2 text-xs text-muted">{{ __('Accepted answers or required terms. The English ones are always accepted; add French equivalents, one per line.') }}</p>
                                @endif
                            </div>
                            <div>
                                <label for="{{ $id }}" class="font-mono text-[11px] text-subtle">{{ __('French') }}</label>
                                <textarea id="{{ $id }}" name="segments[{{ $enc($path) }}]" lang="fr" rows="{{ $long ? min(24, max(4, (int) ceil(mb_strlen($seg['text']) / 90) + substr_count($seg['text'], "\n"))) : 2 }}"
                                    class="mt-1 w-full rounded-lg border border-border-strong bg-bg px-3 py-2 text-sm focus:border-accent focus:outline-none focus:ring-2 focus:ring-accent/30">{{ $mine['text'] ?? '' }}</textarea>
                            </div>
                        </div>
                    @endforeach
                </div>
            </fieldset>
        @endforeach

        <div class="sticky bottom-0 flex flex-wrap gap-3 border-t border-border bg-bg/95 py-4">
            <x-ui.button variant="secondary">{{ __('Save draft') }}</x-ui.button>
            <x-ui.button name="then" value="submit">{{ __('Save and send for review') }}</x-ui.button>
        </div>
    </form>

    @if ($translation && $user->can('translations.review'))
        <section aria-labelledby="rev-h" class="mt-8 rounded-2xl border border-border bg-surface p-5">
            <h2 id="rev-h" class="text-lg font-semibold">{{ __('Review') }}</h2>
            @if (! $canReview)
                <p class="mt-2 text-sm text-muted">{{ __('You made the last edit, so another reviewer must approve this translation.') }}</p>
            @elseif ($translation->status === 'in_review')
                <form method="POST" action="{{ route('admin.translations.review', ['type' => $type, 'id' => $item->getKey()]) }}" class="mt-4 space-y-4">
                    @csrf
                    <x-ui.field :label="__('Note to the translator (required to send back)')" for="note"><x-ui.textarea name="note" maxlength="2000" class="min-h-20" /></x-ui.field>
                    <div class="flex flex-wrap gap-3">
                        <x-ui.button name="decision" value="approve">{{ __('Approve: show to learners') }}</x-ui.button>
                        <x-ui.button name="decision" value="return" variant="secondary">{{ __('Send back') }}</x-ui.button>
                    </div>
                </form>
            @elseif ($translation->status === 'approved' && $compare['stale'] && $compare['complete'])
                <p class="mt-2 text-sm text-muted">{{ __('If the English changes are only small corrections that do not change the meaning, you can confirm the French still matches. Otherwise edit the French above and send it for review.') }}</p>
                <form method="POST" action="{{ route('admin.translations.review', ['type' => $type, 'id' => $item->getKey()]) }}" class="mt-4">
                    @csrf
                    <x-ui.button name="decision" value="confirm" variant="secondary">{{ __('Confirm the French still matches') }}</x-ui.button>
                </form>
            @else
                <p class="mt-2 text-sm text-muted">{{ __('Nothing to review: the translation is not waiting for review.') }}</p>
            @endif
        </section>
    @endif
</x-layouts.admin>
