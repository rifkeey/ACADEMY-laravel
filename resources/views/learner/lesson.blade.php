<x-layouts.site :title="$lesson->title" :noindex="true">
    @php
        $sourceLabel = ['official_standard' => 'Official standard', 'vendor_documentation' => 'Vendor documentation', 'open_resource' => 'Open resource', 'practice_environment' => 'Practice environment'];
    @endphp
    <x-ui.container class="py-8">
        <div class="grid gap-10 lg:grid-cols-[18rem_1fr]">
            <aside class="hidden lg:block">
                <div class="sticky top-24 max-h-[calc(100dvh-7rem)] overflow-y-auto pr-2">
                    <a href="{{ route('learn.course', $slug) }}" class="mb-5 block text-sm font-semibold text-text hover:text-accent">{{ $course['spec']->title }}</a>
                    <x-learn.course-nav :course="$course" :module="$module" :lesson="$lesson" :slug="$slug" />
                </div>
            </aside>

            <div class="min-w-0">
                <details class="mb-6 rounded-xl border border-border bg-surface lg:hidden">
                    <summary class="flex cursor-pointer list-none items-center gap-2 px-4 py-3 text-sm font-medium [&::-webkit-details-marker]:hidden"><x-icon name="list-tree" /> Course contents</summary>
                    <div class="border-t border-border p-4"><x-learn.course-nav :course="$course" :module="$module" :lesson="$lesson" :slug="$slug" /></div>
                </details>

                <article>
                    <p class="text-sm text-accent">Module {{ $module->position }} · {{ $module->title }}</p>
                    <h1 class="mt-2 text-3xl font-semibold tracking-tight sm:text-4xl">{{ $lesson->title }}</h1>
                    <div class="mt-3 flex flex-wrap items-center gap-3 text-sm text-muted">
                        <span class="inline-flex items-center gap-1"><x-icon name="clock" /> {{ $lesson->estimated_minutes }} min</span>
                        @if ($done)<x-ui.pill tone="success">Completed</x-ui.pill>@endif
                    </div>

                    @if ($lesson->objectives)
                        <div class="mt-6 rounded-xl border border-border bg-surface p-5">
                            <p class="text-sm font-semibold">By the end of this lesson you should be able to:</p>
                            <ul class="mt-2 list-disc space-y-1 pl-5 text-sm text-muted">@foreach ($lesson->objectives as $o)<li>{{ $o }}</li>@endforeach</ul>
                        </div>
                    @endif

                    <x-markdown class="mt-8" :source="$lesson->body_md" />

                    {{-- Formative self-checks: not graded or recorded. --}}
                    @if ($lesson->knowledge_checks)
                        <section aria-labelledby="kc-h" class="mt-12 rounded-2xl border border-border bg-surface p-6">
                            <h2 id="kc-h" class="text-lg font-semibold">Check your understanding</h2>
                            <p class="mt-1 text-sm text-muted">Self-check questions. They are not graded.</p>
                            <div class="mt-6 space-y-8">
                                @foreach ($lesson->knowledge_checks as $qi => $check)
                                    <fieldset x-data="knowledgeCheck" data-answer="{{ $check['answerIndex'] }}">
                                        <legend class="font-medium">{{ $qi + 1 }}. {{ $check['question'] }}</legend>
                                        <div class="mt-3 space-y-2">
                                            @foreach ($check['options'] as $oi => $option)
                                                <label data-index="{{ $oi }}" :class="optionClass" class="flex cursor-pointer items-center gap-3 rounded-lg border px-3 py-2.5 text-sm transition-colors">
                                                    <input type="radio" name="kc-{{ $qi }}" data-index="{{ $oi }}" class="accent-[var(--accent)]" @change="choose" :disabled="answered">
                                                    <span class="flex-1">{{ $option }}</span>
                                                </label>
                                            @endforeach
                                        </div>
                                        <p x-show="answered" x-cloak role="status" class="mt-3 text-sm text-muted">
                                            <span x-show="isCorrect" class="font-semibold text-success">Correct. </span><span x-show="! isCorrect" class="font-semibold text-danger">Not quite. </span>{{ $check['explanation'] }}
                                            <button type="button" class="ml-2 text-text underline" @click="reset">Try again</button>
                                        </p>
                                    </fieldset>
                                @endforeach
                            </div>
                        </section>
                    @endif

                    @if ($lesson->sources->isNotEmpty())
                        <section aria-labelledby="refs-h" class="mt-12">
                            <h2 id="refs-h" class="text-lg font-semibold">References and further reading</h2>
                            <p class="mt-1 text-sm text-muted">External sources are linked for further study. Lesson text is original AMANIQ instruction.</p>
                            <ul class="mt-4 space-y-3">
                                @foreach ($lesson->sources as $s)
                                    <li class="rounded-lg border border-border p-3 text-sm">
                                        <a href="{{ $s->url }}" target="_blank" rel="noopener noreferrer" class="inline-flex items-center gap-1 font-medium text-accent-strong hover:underline">{{ $s->title }} <x-icon name="external-link" class="size-3.5" /><span class="sr-only">(opens in a new tab)</span></a>
                                        <p class="mt-0.5 text-xs text-subtle">{{ $s->publisher }} · {{ $sourceLabel[$s->source_type] ?? $s->source_type }}</p>
                                        @if ($s->version_note)<p class="mt-1 text-xs text-muted">{{ $s->version_note }}</p>@endif
                                    </li>
                                @endforeach
                            </ul>
                        </section>
                    @endif
                </article>

                <div class="mt-12 flex flex-col gap-4 border-t border-border pt-6 sm:flex-row sm:items-center sm:justify-between">
                    @if ($prev)
                        <x-ui.button :href="route('learn.lesson', [$slug, $prev->id])" variant="ghost" size="sm"><x-icon name="arrow-left" /> {{ $prev->title }}</x-ui.button>
                    @else<span></span>@endif
                    <div class="flex flex-wrap gap-2">
                        @if (! $done)
                            <form method="POST" action="{{ route('learn.lesson.complete', [$slug, $lesson->id]) }}">
                                @csrf
                                @if ($next)<input type="hidden" name="next" value="{{ $next->id }}">@endif
                                <x-ui.button>{{ $next ? 'Mark complete and continue' : 'Mark lesson complete' }}</x-ui.button>
                            </form>
                        @elseif ($next)
                            <x-ui.button :href="route('learn.lesson', [$slug, $next->id])">Next: {{ $next->title }} <x-icon name="arrow-right" /></x-ui.button>
                        @else
                            <x-ui.button :href="route('learn.course', $slug)">Back to course overview</x-ui.button>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </x-ui.container>
</x-layouts.site>
