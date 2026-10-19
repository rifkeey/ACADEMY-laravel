{{-- Final examination overview: rules, eligibility (decided on the server) and attempt history. --}}
<x-layouts.site :title="'Examination: '.$spec->title" :noindex="true">
    <x-ui.container class="py-12">
        <nav aria-label="Breadcrumb" class="mb-4 text-sm text-muted">
            <a href="{{ route('learn.course', $slug) }}" class="hover:text-text">{{ $spec->title }}</a> <span aria-hidden="true">/</span> <span class="text-text">Final examination</span>
        </nav>
        <x-ui.page-header eyebrow="Final examination" :title="$exam->title ?? $spec->title" />

        <div class="mt-8 grid gap-8 lg:grid-cols-[1fr_20rem]">
            <div class="space-y-6">
                @if ($exam)
                    <x-ui.card class="p-6">
                        <h2 class="font-semibold">Rules</h2>
                        <ul class="mt-3 space-y-2 text-sm text-muted">
                            <li class="flex gap-2"><x-icon name="file-question" class="mt-0.5 size-4 shrink-0 text-accent" />{{ $exam->question_count }} questions drawn from the programme's question bank, balanced across topics. Questions and options are shuffled for each attempt.</li>
                            <li class="flex gap-2"><x-icon name="clock" class="mt-0.5 size-4 shrink-0 text-accent" />{{ $exam->duration_minutes }} minutes. The timer runs on the server and your answers are submitted automatically when time runs out.</li>
                            <li class="flex gap-2"><x-icon name="gauge" class="mt-0.5 size-4 shrink-0 text-accent" />Pass mark {{ $exam->pass_percent }}% ({{ (int) ceil($exam->question_count * $exam->pass_percent / 100) }} of {{ $exam->question_count }} correct).</li>
                            <li class="flex gap-2"><x-icon name="flag" class="mt-0.5 size-4 shrink-0 text-accent" />Up to {{ $exam->max_attempts }} attempts, at least {{ $exam->cooldown_hours }} hours apart.</li>
                            <li class="flex gap-2"><x-icon name="lock" class="mt-0.5 size-4 shrink-0 text-accent" />Work on your own without outside help. Leaving the page does not stop the timer.</li>
                        </ul>
                    </x-ui.card>
                @endif

                @if ($openAttempt)
                    <x-ui.alert tone="warning" title="You have an attempt in progress">It expires at {{ $openAttempt->expires_at->format('H:i') }} UTC. Resume it now.</x-ui.alert>
                    <x-ui.button :href="route('exam.take', [$slug, $openAttempt])">Resume attempt</x-ui.button>
                @elseif ($canStart)
                    <form method="POST" action="{{ route('exam.start', $slug) }}" class="space-y-3">
                        @csrf
                        <p class="text-sm text-muted">When you start, the timer begins immediately.</p>
                        <x-ui.button size="lg">Start attempt {{ $attemptsUsed + 1 }} of {{ $exam->max_attempts }}</x-ui.button>
                    </form>
                @else
                    <x-ui.alert tone="{{ $requirements['examPassed'] ? 'success' : 'info' }}">{{ $reason }}</x-ui.alert>
                @endif

                @if ($attempts->isNotEmpty())
                    <section aria-labelledby="attempts-h">
                        <h2 id="attempts-h" class="font-semibold">Your attempts</h2>
                        <div class="mt-3">
                            <x-ui.table :head="['Attempt', 'Started', 'Score', 'Result', '']" caption="Your examination attempts">
                                @foreach ($attempts as $a)
                                    <tr>
                                        <x-ui.td>{{ $a->attempt_number }}</x-ui.td>
                                        <x-ui.td>{{ $a->started_at->format('j M Y, H:i') }} UTC</x-ui.td>
                                        <x-ui.td class="tabular-nums">{{ $a->status === 'submitted' ? $a->correct_count.'/'.$a->total_count : '—' }}</x-ui.td>
                                        <x-ui.td>
                                            @if ($a->status !== 'submitted')<x-ui.pill tone="warning">In progress</x-ui.pill>
                                            @elseif ($a->passed)<x-ui.pill tone="success">Passed</x-ui.pill>
                                            @else<x-ui.pill tone="danger">Not passed</x-ui.pill>@endif
                                        </x-ui.td>
                                        <x-ui.td>@if ($a->status === 'submitted')<a href="{{ route('exam.result', [$slug, $a]) }}" class="text-accent-strong hover:underline">Results</a>@endif</x-ui.td>
                                    </tr>
                                @endforeach
                            </x-ui.table>
                        </div>
                    </section>
                @endif
            </div>

            <aside>
                <x-ui.card class="space-y-4 p-5">
                    <h2 class="font-semibold">Eligibility</h2>
                    @php $r = $requirements; @endphp
                    <x-ui.progress label="Required lessons" :value="$r['lessonsTotal'] ? $r['lessonsDone'] / $r['lessonsTotal'] * 100 : 0" />
                    <x-ui.progress label="Required labs" :value="$r['labsTotal'] ? $r['labsPassed'] / $r['labsTotal'] * 100 : 100" />
                    <p class="text-sm text-muted">{{ $r['lessonsDone'] }}/{{ $r['lessonsTotal'] }} lessons · {{ $r['labsPassed'] }}/{{ $r['labsTotal'] }} labs</p>
                    @if ($exam)<p class="text-sm text-muted">Attempts used: {{ $attemptsUsed }} of {{ $exam->max_attempts }}</p>@endif
                </x-ui.card>
            </aside>
        </div>
    </x-ui.container>
</x-layouts.site>
