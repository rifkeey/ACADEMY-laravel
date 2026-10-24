<x-layouts.site :title="__('Your dashboard')" :noindex="true">
    @php $user = auth()->user(); $done = $programmes->filter(fn ($p) => $p['requirements']['complete'])->count(); @endphp
    <x-ui.container class="py-12">
        <x-ui.page-header :eyebrow="__('Dashboard')" :title="(explode(' ', $user->name)[0] ?? '') !== '' ? __('Welcome back, :name', ['name' => explode(' ', $user->name)[0]]) : __('Welcome back')" :description="__('Pick up where you left off, track requirements and download your credentials.')">
            <x-slot:actions>
                <x-ui.button :href="route('portfolio')" variant="ghost" size="sm">{{ __('Portfolio') }}</x-ui.button>
                <x-ui.button :href="route('dashboard.payments')" variant="ghost" size="sm">{{ __('Payment history') }}</x-ui.button>
                <x-ui.button :href="route('account')" variant="secondary" size="sm">{{ __('Account') }}</x-ui.button>
            </x-slot:actions>
        </x-ui.page-header>

        <div class="mt-8 grid gap-4 sm:grid-cols-4">
            <x-ui.stat :label="__('Enrolled programmes')" :value="$programmes->count()" />
            <x-ui.stat :label="__('Completed')" :value="$done" />
            <x-ui.stat :label="__('Badges earned')" :value="$badges->count()" />
            <x-ui.stat :label="__('Certificates')" :value="$certificates->count()" />
        </div>

        <section class="mt-12" aria-labelledby="programmes-h">
            <h2 id="programmes-h" class="text-xl font-semibold">{{ __('Your programmes') }}</h2>
            @if ($programmes->isEmpty())
                <div class="mt-4">
                    <x-ui.empty :title="__('You are not enrolled yet')" icon="graduation-cap">
                        {{ __('Choose a specialization to start learning. Your progress, labs and results will appear here.') }}
                        <x-slot:action><x-ui.button :href="lroute('programmes.index')">{{ __('Explore programmes') }}</x-ui.button></x-slot:action>
                    </x-ui.empty>
                </div>
            @else
                <div class="mt-4 grid gap-5 lg:grid-cols-2">
                    @foreach ($programmes as $p)
                        @php $r = $p['requirements']; $spec = $p['spec']; @endphp
                        <x-ui.card class="p-6">
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <h3 class="text-lg font-semibold">{{ $spec->title }}</h3>
                                    <p class="mt-1 text-sm text-muted">{{ __(':done/:total lessons · :passed/:labs labs', ['done' => $r['lessonsDone'], 'total' => $r['lessonsTotal'], 'passed' => $r['labsPassed'], 'labs' => $r['labsTotal']]) }} · {{ $r['examPassed'] ? __('exam passed') : ($r['examEligible'] ? __('exam unlocked') : __('exam locked')) }}</p>
                                </div>
                                @if ($r['complete'])<x-ui.pill tone="success">{{ __('Completed') }}</x-ui.pill>@elseif ($r['examEligible'])<x-ui.pill tone="accent">{{ __('Exam ready') }}</x-ui.pill>@else<x-ui.pill>{{ __('In progress') }}</x-ui.pill>@endif
                            </div>
                            <div class="mt-5 space-y-3">
                                <x-ui.progress :label="__('Lessons')" :value="$r['lessonsTotal'] ? $r['lessonsDone'] / $r['lessonsTotal'] * 100 : 0" />
                                <x-ui.progress :label="__('Labs')" :value="$r['labsTotal'] ? $r['labsPassed'] / $r['labsTotal'] * 100 : 100" />
                            </div>
                            <div class="mt-6 flex flex-wrap gap-2">
                                @if ($r['complete'] && $p['certificate'])
                                    <x-ui.button :href="route('certificates.pdf', $p['certificate'])" size="sm"><x-icon name="file-text" /> {{ __('Download certificate') }}</x-ui.button>
                                @elseif ($p['nextLesson'])
                                    <x-ui.button :href="route('learn.lesson', [$spec->slug, $p['nextLesson']->id])" size="sm">{{ __('Continue: :lesson', ['lesson' => Str::limit($p['nextLesson']->title, 32)]) }} <x-icon name="arrow-right" /></x-ui.button>
                                @elseif ($r['examEligible'] && ! $r['examPassed'])
                                    <x-ui.button :href="route('exam.show', $spec->slug)" size="sm">{{ __('Go to the final examination') }}</x-ui.button>
                                @else
                                    <x-ui.button :href="route('learn.course', $spec->slug)" size="sm">{{ __('Open programme') }}</x-ui.button>
                                @endif
                                <x-ui.button :href="route('learn.course', $spec->slug)" variant="ghost" size="sm"><x-icon name="book-open" /> {{ __('Course overview') }}</x-ui.button>
                            </div>
                        </x-ui.card>
                    @endforeach
                </div>
            @endif
        </section>

        <section class="mt-12 grid gap-8 lg:grid-cols-2">
            <div aria-labelledby="creds-h">
                <h2 id="creds-h" class="text-xl font-semibold">{{ __('Badges and certificates') }}</h2>
                @if ($certificates->isEmpty())
                    <div class="mt-4"><x-ui.empty :title="__('No credentials yet')" icon="award">{{ __('Complete every lesson and lab, then pass the final examination to earn your badge and certificate.') }}</x-ui.empty></div>
                @else
                    <ul class="mt-4 space-y-3">
                        @foreach ($certificates as $c)
                            @php $badge = $badges->firstWhere('enrolment_id', $c->enrolment_id)?->badge; @endphp
                            <li>
                                <x-ui.card class="flex items-center gap-4 p-4">
                                    @if ($badge)<x-credential-badge :name="$badge->name" :hue="$badge->hue" :icon="$badge->icon" :size="56" />@endif
                                    <div class="min-w-0 flex-1">
                                        <p class="font-semibold">{{ $c->programme_title }}</p>
                                        <p class="text-xs text-muted">{{ $c->certificate_number }} · {{ __('issued :date', ['date' => $c->issued_at->translatedFormat('j M Y')]) }}</p>
                                        @if ($c->status === 'revoked')<x-ui.pill tone="danger" class="mt-1">{{ __('Revoked') }}</x-ui.pill>@endif
                                    </div>
                                    <div class="flex flex-col gap-1 text-right text-sm">
                                        <a href="{{ route('certificates.pdf', $c) }}" class="font-semibold text-accent hover:underline">{{ __('PDF') }}</a>
                                        <a href="{{ lroute('verify.show', $c->credential_id) }}" class="text-muted hover:text-text">{{ __('Verify') }}</a>
                                    </div>
                                </x-ui.card>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>
            <div aria-labelledby="attempts-h">
                <h2 id="attempts-h" class="text-xl font-semibold">{{ __('Recent examination attempts') }}</h2>
                @if ($attempts->isEmpty())
                    <p class="mt-4 text-sm text-muted">{{ __('No attempts yet.') }}</p>
                @else
                    <div class="mt-4">
                        <x-ui.table :head="[__('Exam'), __('Date'), __('Score'), __('Result')]" :caption="__('Recent examination attempts')">
                            @foreach ($attempts as $a)
                                <tr>
                                    <x-ui.td>{{ $a->exam->title }}</x-ui.td>
                                    <x-ui.td>{{ $a->started_at->translatedFormat('j M Y') }}</x-ui.td>
                                    <x-ui.td>{{ $a->status === 'submitted' ? "{$a->correct_count}/{$a->total_count}" : __('In progress') }}</x-ui.td>
                                    <x-ui.td>@if ($a->status !== 'submitted')<x-ui.pill>{{ __('In progress') }}</x-ui.pill>@elseif ($a->passed)<x-ui.pill tone="success">{{ __('Pass') }}</x-ui.pill>@else<x-ui.pill tone="danger">{{ __('Fail') }}</x-ui.pill>@endif</x-ui.td>
                                </tr>
                            @endforeach
                        </x-ui.table>
                    </div>
                @endif
            </div>
        </section>
    </x-ui.container>
</x-layouts.site>
