@php
    use App\Support\Money;
    $priceText = $price ? Money::format($price->amount_minor, $price->currency) : __('Price on request');
    $lessonCount = $modules->sum(fn ($m) => $m->lessons->count());
    $difficulty = ['foundation' => __('Foundation'), 'intermediate' => __('Intermediate'), 'advanced' => __('Advanced')];
    $user = auth()->user();
    $jsonLd = ['@context' => 'https://schema.org', '@type' => 'Course', 'name' => $spec->title, 'description' => $spec->summary,
        'provider' => ['@type' => 'Organization', 'name' => 'AMANIQ Academy', 'parentOrganization' => ['@type' => 'Organization', 'name' => 'AMANIQ Projects Limited', 'url' => 'https://amaniqgroup.com']]];
@endphp
<x-layouts.site :title="__(':title programme', ['title' => $spec->title])" :description="$spec->summary">
    <x-slot:head>
        {{-- Course structured data. JSON_HEX_TAG prevents "</script>" breaking out of the tag. --}}
        <script type="application/ld+json" nonce="{{ Vite::cspNonce() }}">{!! json_encode($jsonLd, JSON_HEX_TAG | JSON_UNESCAPED_SLASHES) !!}</script>
    </x-slot:head>

    @php
        $cta = $enrolled
            ? ['href' => route('learn.course', $spec->slug), 'label' => __('Continue learning'), 'variant' => 'primary']
            : ($spec->enrolment_open
                ? ['href' => route('checkout.show', $spec), 'label' => __('Enrol now · :price', ['price' => $priceText]), 'variant' => 'primary']   // guests are sent to sign in, then back here
                : ['href' => lroute('contact', ['kind' => 'programme_interest', 'programme' => $spec->slug]), 'label' => __('Register interest'), 'variant' => 'secondary']);
    @endphp

    <section class="border-b border-border bg-surface/50">
        <x-ui.container class="grid gap-10 py-14 lg:grid-cols-[1.5fr_1fr]">
            <div>
                <nav aria-label="{{ __('Breadcrumb') }}" class="text-sm text-subtle"><a href="{{ lroute('programmes.index') }}" class="hover:text-text">{{ __('Programmes') }}</a> / <span class="text-muted">{{ $spec->title }}</span></nav>
                <div class="mt-4 flex flex-wrap gap-2">
                    <x-ui.pill tone="accent">{{ __($categories[$spec->category] ?? $spec->category) }}</x-ui.pill>
                    <x-ui.pill>{{ $spec->level }}</x-ui.pill>
                    <x-ui.pill>{{ __('Version :n', ['n' => $version->version]) }}</x-ui.pill>
                </div>
                <h1 class="mt-4 text-4xl font-semibold tracking-tight">{{ $spec->title }}</h1>
                <p class="mt-4 text-lg leading-relaxed text-muted">{{ $spec->description }}</p>
                @if ($spec->content_status !== 'complete')
                    <x-ui.alert tone="warning" :title="__('Curriculum in development')" class="mt-6">{{ __('Module structure and starter lessons are published below so you can see what is planned. Enrolment opens once every lesson, lab and the final examination have been completed and reviewed.') }}</x-ui.alert>
                @endif
                <div class="mt-8 flex flex-wrap items-center gap-4">
                    <x-ui.button :href="$cta['href']" :variant="$cta['variant']" size="lg">{{ $cta['label'] }}</x-ui.button>
                    <p class="text-sm text-subtle">{{ __('Last updated :date', ['date' => $spec->updated_at->translatedFormat('F Y')]) }}</p>
                </div>
            </div>
            <x-ui.card class="h-fit p-6">
                <p class="text-sm text-muted">{{ __('Price') }}</p>
                <p class="text-3xl font-semibold tabular-nums">{{ $priceText }}</p>
                <p class="mt-1 text-xs text-subtle">{{ __('One-off payment. Includes access to course version :n.', ['n' => $version->version]) }}</p>
                <ul class="mt-5 space-y-2.5 text-sm">
                    <li class="flex items-center gap-2"><x-icon name="layers" class="text-accent" /> {{ __(':modules modules · :lessons published lessons', ['modules' => $modules->count(), 'lessons' => $lessonCount]) }}</li>
                    <li class="flex items-center gap-2"><x-icon name="flask-conical" class="text-accent" /> {{ __($spec->content_status !== 'complete' ? ':count hands-on labs (more planned)' : ':count hands-on labs', ['count' => $labs->count()]) }}</li>
                    <li class="flex items-center gap-2"><x-icon name="clock" class="text-accent" /> {{ __('About :hours hours (estimate)', ['hours' => $spec->estimated_hours]) }}</li>
                    <li class="flex items-center gap-2"><x-icon name="file-check-2" class="text-accent" /> {{ $exam ? __(':count-question exam · pass mark :pass%', ['count' => $exam->question_count, 'pass' => $exam->pass_percent]) : __('Final examination') }}</li>
                </ul>
                <div class="mt-5 border-t border-border pt-4">
                    <p class="text-sm font-semibold">{{ __('What is included') }}</p>
                    <ul class="mt-2 space-y-1.5 text-sm text-muted">
                        @foreach ($spec->inclusions as $i)<li class="flex gap-2"><x-icon name="check-circle-2" class="mt-0.5 shrink-0 text-success" /> {{ $i }}</li>@endforeach
                    </ul>
                </div>
            </x-ui.card>
        </x-ui.container>
    </section>

    <x-ui.container class="grid gap-12 py-14 lg:grid-cols-[1.5fr_1fr]">
        <div class="space-y-14">
            <section aria-labelledby="audience">
                <h2 id="audience" class="text-2xl font-semibold">{{ __('Who it is for') }}</h2>
                <div class="mt-5 grid gap-6 sm:grid-cols-2">
                    <div><h3 class="text-sm font-semibold uppercase tracking-wide text-muted">{{ __('Intended audience') }}</h3><ul class="mt-3 space-y-2 text-sm">@foreach ($spec->audience as $a)<li>{{ $a }}</li>@endforeach</ul></div>
                    <div><h3 class="text-sm font-semibold uppercase tracking-wide text-muted">{{ __('Prerequisites') }}</h3><ul class="mt-3 space-y-2 text-sm">@foreach ($spec->prerequisites as $a)<li>{{ $a }}</li>@endforeach</ul></div>
                </div>
            </section>

            <section aria-labelledby="skills">
                <h2 id="skills" class="text-2xl font-semibold">{{ __('Skills you will build') }}</h2>
                <ul class="mt-5 grid gap-3 sm:grid-cols-2">@foreach ($spec->outcomes as $o)<li class="flex gap-2 text-sm"><x-icon name="check-circle-2" class="mt-0.5 shrink-0 text-accent" /> {{ $o }}</li>@endforeach</ul>
            </section>

            <section aria-labelledby="curriculum">
                <h2 id="curriculum" class="text-2xl font-semibold">{{ __('Curriculum') }}</h2>
                <ol class="mt-5 space-y-3">
                    @foreach ($modules as $m)
                        <li>
                            <details class="group rounded-xl border border-border bg-surface">
                                <summary class="flex cursor-pointer list-none items-start justify-between gap-4 p-5 [&::-webkit-details-marker]:hidden">
                                    <div>
                                        <p class="text-xs font-semibold uppercase tracking-wide text-accent">{{ __('Module :n', ['n' => $m->position]) }}</p>
                                        <h3 class="mt-1 font-semibold">{{ $m->title }}</h3>
                                        <p class="mt-1 text-sm text-muted">{{ $m->purpose }}</p>
                                    </div>
                                    <div class="flex shrink-0 flex-col items-end gap-1">
                                        @if ($m->content_status !== 'complete')<x-ui.pill :tone="$m->content_status === 'outline' ? 'neutral' : 'warning'">{{ $m->content_status === 'outline' ? __('Outline') : __('Starter content') }}</x-ui.pill>@endif
                                        <span aria-hidden="true" class="text-subtle transition-transform group-open:rotate-45">+</span>
                                    </div>
                                </summary>
                                <div class="space-y-4 border-t border-border p-5 text-sm">
                                    @if ($m->objectives)
                                        <div><p class="font-semibold">{{ __('Learning objectives') }}</p><ul class="mt-2 list-disc space-y-1 pl-5 text-muted">@foreach ($m->objectives as $o)<li>{{ $o }}</li>@endforeach</ul></div>
                                    @endif
                                    @if ($m->lessons->isNotEmpty())
                                        <div><p class="font-semibold">{{ __('Lessons') }}</p><ul class="mt-2 space-y-1 text-muted">@foreach ($m->lessons as $l)<li>{{ $l->title }} <span class="text-subtle">· {{ __(':n min', ['n' => $l->estimated_minutes]) }}</span></li>@endforeach</ul></div>
                                    @else
                                        <p class="text-subtle">{{ __('Lessons for this module are being authored.') }}</p>
                                    @endif
                                    @if ($m->labs->isNotEmpty())
                                        <div><p class="font-semibold">{{ __('Labs') }}</p><ul class="mt-2 space-y-1 text-muted">@foreach ($m->labs as $l)<li>{{ $l->title }}</li>@endforeach</ul></div>
                                    @endif
                                </div>
                            </details>
                        </li>
                    @endforeach
                </ol>
            </section>

            @if ($labs->isNotEmpty())
                <section aria-labelledby="labs">
                    <h2 id="labs" class="text-2xl font-semibold">{{ __('Practical labs') }}</h2>
                    <p class="mt-2 text-sm text-muted">{{ __('All labs are simulations on synthetic data. Answers are validated on our servers.') }}</p>
                    <div class="mt-5 grid gap-4 sm:grid-cols-2">
                        @foreach ($labs as $i => $l)
                            <x-ui.card class="p-5">
                                <div class="flex items-center gap-2 text-xs text-subtle"><span>{{ __('Lab :n', ['n' => $i + 1]) }}</span> · <span>{{ $difficulty[$l->difficulty] ?? $l->difficulty }}</span> · <span>{{ __('~:n min', ['n' => $l->estimated_minutes]) }}</span></div>
                                <h3 class="mt-2 font-semibold">{{ $l->title }}</h3>
                                <p class="mt-1 text-sm text-muted">{{ $l->summary }}</p>
                            </x-ui.card>
                        @endforeach
                    </div>
                </section>
            @endif

            <section aria-labelledby="assessment">
                <h2 id="assessment" class="text-2xl font-semibold">{{ __('Assessment and completion') }}</h2>
                <div class="mt-5 space-y-3 text-sm text-muted">
                    <p>{{ __('To complete the programme and receive the badge and certificate you must:') }}</p>
                    <ol class="list-decimal space-y-1.5 pl-5">
                        <li>{{ __('complete every required lesson;') }}</li>
                        <li>{{ __('pass every required lab (each lab states its pass threshold);') }}</li>
                        <li>{{ __($exam ? 'pass the final examination with at least :pass%: :count randomised questions in :minutes minutes, graded on the server, up to :attempts attempts.' : 'pass the final examination with at least :pass%: :count randomised questions in :minutes minutes, graded on the server.', ['pass' => $exam->pass_percent ?? 75, 'count' => $exam->question_count ?? 40, 'minutes' => $exam->duration_minutes ?? 60, 'attempts' => $exam->max_attempts ?? 3]) }}</li>
                    </ol>
                    <p>{{ __('The examination unlocks only when the lesson and lab requirements are met.') }}</p>
                </div>
            </section>
        </div>

        <aside class="space-y-6">
            @if ($badge)
                <x-ui.card class="p-6">
                    <div class="flex items-center gap-4">
                        <x-credential-badge :name="$badge->name" :hue="$badge->hue" :icon="$badge->icon" :size="84" />
                        <div><p class="font-semibold">{{ $badge->name }}</p><p class="mt-1 text-xs text-muted">{{ __('Digital badge and verifiable PDF certificate') }}</p></div>
                    </div>
                    <p class="mt-4 text-sm text-muted">{{ $badge->criteria }}</p>
                    <p class="mt-3 text-xs text-subtle">{{ __('Issued by AMANIQ Academy. A certificate of completion, not an accredited qualification or vendor certification.') }}</p>
                </x-ui.card>
            @endif
            @if ($spec->faqs)
                <x-ui.card class="p-6">
                    <h2 class="font-semibold">{{ __('Questions about this programme') }}</h2>
                    <dl class="mt-4 space-y-4 text-sm">@foreach ($spec->faqs as $f)<div><dt class="font-medium">{{ $f['q'] }}</dt><dd class="mt-1 text-muted">{{ $f['a'] }}</dd></div>@endforeach</dl>
                </x-ui.card>
            @endif
            <div class="sticky top-24"><x-ui.button :href="$cta['href']" :variant="$cta['variant']" size="lg">{{ $cta['label'] }}</x-ui.button></div>
        </aside>
    </x-ui.container>
</x-layouts.site>
