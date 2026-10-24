{{--
    Taking an attempt. The page only receives question text and option ids/text from the attempt
    snapshot: never which options are correct. It is a plain form, so it works without JavaScript;
    the examTaker component adds one-at-a-time navigation, flags and the auto-submit.
--}}
<x-layouts.site :title="__('Examination in progress')" :noindex="true">
    <x-ui.container class="py-8">
        <div x-data="examTaker" data-total="{{ count($questions) }}" data-attempt="{{ $attempt->public_id }}" @expired="expired" @change="track">
            <div class="sticky top-16 z-20 -mx-4 mb-6 flex flex-wrap items-center justify-between gap-3 border-b border-border bg-bg/95 px-4 py-3 backdrop-blur sm:mx-0 sm:rounded-xl sm:border">
                <p class="text-sm"><span class="font-semibold">{{ __('Attempt :n', ['n' => $attempt->attempt_number]) }}</span> · {!! __(':answered/:total answered', ['answered' => '<span x-text="answeredCount"></span>', 'total' => count($questions)]) !!}</p>
                <div x-data="countdown" data-expires-at="{{ $attempt->expires_at->toIso8601ZuluString() }}" role="timer" aria-live="off" class="inline-flex items-center gap-1.5 rounded-lg border px-3 py-1 font-mono text-sm tabular-nums" :class="urgent ? 'border-danger/50 text-danger' : 'border-border text-text'">
                    <x-icon name="clock" class="size-4" /><span x-text="text">{{ $attempt->expires_at->format('H:i') }} UTC</span>
                </div>
            </div>

            <div class="grid gap-8 lg:grid-cols-[1fr_16rem]">
                <form x-ref="form" method="POST" action="{{ route('exam.submit', [$slug, $attempt]) }}" class="min-w-0">
                    @csrf
                    @foreach ($questions as $i => $q)
                        @php $multi = $q['kind'] === 'multiple'; $pos = $q['position']; @endphp
                        <fieldset x-show="isCurrent({{ $i }})" @if ($i > 0) x-cloak @endif class="min-w-0 rounded-2xl border border-border bg-surface p-6">
                            <legend class="sr-only">{{ __('Question :n', ['n' => $pos]) }}</legend>
                            <p class="text-xs font-semibold uppercase tracking-wide text-subtle">{{ __('Question :n of :total', ['n' => $pos, 'total' => count($questions)]) }}{{ $multi ? ' · '.__('select all that apply') : '' }}</p>
                            <x-markdown class="mt-2" :compact="true" :source="$q['prompt']" />
                            <div class="mt-5 space-y-2">
                                @foreach ($q['options'] as $o)
                                    <label for="q{{ $pos }}-o{{ $o['id'] }}" class="flex cursor-pointer items-start gap-3 rounded-lg border border-border px-3 py-3 text-sm hover:bg-elevated has-[:checked]:border-accent/60 has-[:checked]:bg-accent/5">
                                        <input id="q{{ $pos }}-o{{ $o['id'] }}" type="{{ $multi ? 'checkbox' : 'radio' }}" name="responses[{{ $pos }}][]" value="{{ $o['id'] }}" data-position="{{ $pos }}" class="mt-0.5 accent-[var(--accent)]">
                                        <span>{{ $o['text'] }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </fieldset>
                    @endforeach

                    <div class="mt-6 flex flex-wrap items-center justify-between gap-3">
                        <div class="flex gap-2">
                            <x-ui.button type="button" variant="secondary" size="sm" @click="prev" x-bind:disabled="isFirst"><x-icon name="arrow-left" class="size-4" /> {{ __('Previous') }}</x-ui.button>
                            <x-ui.button type="button" variant="secondary" size="sm" @click="next" x-bind:disabled="isLast">{{ __('Next') }} <x-icon name="arrow-right" class="size-4" /></x-ui.button>
                        </div>
                        <button type="button" @click="toggleFlag" class="inline-flex items-center gap-1.5 text-sm text-muted hover:text-text" :aria-pressed="isFlagged">
                            <x-icon name="flag" class="size-4" /><span x-text="isFlagged ? {{ Js::from(__('Flagged for review')) }} : {{ Js::from(__('Flag for review')) }}">{{ __('Flag for review') }}</span>
                        </button>
                    </div>

                    <div class="mt-8 rounded-2xl border border-border bg-surface p-6">
                        <div x-show="! confirming">
                            <p class="text-sm text-muted">{{ __('When you have finished, review and submit your answers. You cannot change them afterwards.') }}</p>
                            <x-ui.button type="button" class="mt-3" @click="review">{{ __('Finish attempt') }}</x-ui.button>
                        </div>
                        <div x-show="confirming" x-cloak role="alertdialog" aria-labelledby="confirm-h">
                            <h2 id="confirm-h" class="font-semibold">{{ __('Submit your answers?') }}</h2>
                            <p class="mt-1 text-sm text-muted"><span x-show="unanswered > 0">{!! __(':count question(s) are unanswered and will be marked incorrect.', ['count' => '<span x-text="unanswered"></span>']) !!} </span>{{ __('This cannot be undone.') }}</p>
                            <div class="mt-4 flex gap-2">
                                <x-ui.button type="button" variant="ghost" size="sm" @click="keepWorking">{{ __('Keep working') }}</x-ui.button>
                                <x-ui.button type="button" size="sm" @click="submit" x-bind:disabled="submitting">{{ __('Submit answers') }}</x-ui.button>
                            </div>
                        </div>
                        <noscript><x-ui.button class="mt-3">{{ __('Submit answers') }}</x-ui.button></noscript>
                    </div>
                </form>

                <aside aria-label="{{ __('Question navigator') }}">
                    <div class="sticky top-36 rounded-2xl border border-border bg-surface p-4">
                        <p class="text-sm font-semibold">{{ __('Questions') }}</p>
                        <div class="mt-3 grid grid-cols-8 gap-1.5 lg:grid-cols-5">
                            @foreach ($questions as $i => $q)
                                <button type="button" data-index="{{ $i }}" @click="go" :class="navClass({{ $i }})" :aria-current="isCurrent({{ $i }}) ? 'step' : null" class="relative h-9 rounded-md border text-xs font-medium tabular-nums" aria-label="{{ __('Question :n', ['n' => $q['position']]) }}">
                                    {{ $q['position'] }}<span x-show="isFlaggedAt({{ $i }})" x-cloak class="absolute -right-0.5 -top-0.5 size-2 rounded-full bg-warning"></span>
                                </button>
                            @endforeach
                        </div>
                        <p class="mt-3 text-xs text-subtle">{{ __('Highlighted: current. Dot: flagged.') }}</p>
                    </div>
                </aside>
            </div>
        </div>
    </x-ui.container>
</x-layouts.site>
