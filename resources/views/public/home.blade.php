<x-layouts.site>
    @php
        $differentiators = [
            ['flask-conical', __('Labs that check your work'), __('Every programme is built around simulated investigations, query consoles and configuration exercises. Your answers are validated on our servers, not ticked off as read.')],
            ['scroll-text', __('Sourced, original teaching'), __('Lessons are written by AMANIQ and linked to official frameworks and documentation such as NIST, MITRE ATT&CK, OWASP and vendor references, with a dated source register.')],
            ['file-check-2', __('Fair, server-graded assessment'), __('Final examinations draw randomised questions from a reviewed bank, are graded on the server, and require 75% to pass. Requirements are the same for everyone.')],
            ['qr-code', __('Credentials anyone can check'), __('Each certificate carries a unique verification link and QR code that resolves to a live record, including whether it has been revoked.')],
        ];
        $steps = [
            [__('Choose a specialization'), __('Compare programmes, outcomes and prerequisites, then enrol.')],
            [__('Learn and practise'), __('Work through lessons with knowledge checks and complete hands-on simulated labs.')],
            [__('Pass the final examination'), __('Once all required lessons and labs are complete, sit a 40-question examination. The pass mark is 75%.')],
            [__('Earn your badge and certificate'), __('Receive a digital badge and a downloadable PDF certificate with a public verification page.')],
        ];
        $faqs = [
            [__('Are AMANIQ certificates professional certifications?'), __('No. They are certificates of completion issued by AMANIQ Academy. They are not government-accredited qualifications or vendor certifications, and we never describe them as such.')],
            [__('Do I need paid software or a cloud subscription?'), __('No. Essential lab work runs inside the AMANIQ platform on synthetic data. Any optional external exercise is clearly labelled.')],
            [__('Are the labs real hacking environments?'), __('Labs are clearly labelled simulations: log explorers, a KQL query simulator, firewall policy and configuration workbenches. They never connect to real systems.')],
            [__('Can my organisation train a team?'), __('Yes. Tell us about your team on the corporate training page and we will discuss options.')],
        ];
    @endphp

    {{-- Hero --}}
    <section class="relative overflow-hidden border-b border-border">
        <div aria-hidden="true" class="pointer-events-none absolute inset-0 bg-[radial-gradient(60%_50%_at_70%_0%,rgba(45,212,191,0.12),transparent_70%)]"></div>
        <x-ui.container class="relative grid gap-12 py-20 sm:py-28 lg:grid-cols-[1.2fr_1fr] lg:items-center">
            <div>
                <p class="text-sm font-semibold uppercase tracking-[0.16em] text-accent">{{ __('Cybersecurity education, built on practice') }}</p>
                <h1 class="mt-4 text-4xl font-semibold leading-[1.1] tracking-tight sm:text-5xl lg:text-6xl">{{ __('Learn security the way it is actually done.') }}</h1>
                <p class="mt-6 max-w-xl text-lg leading-relaxed text-muted">{{ __('Structured programmes for security operations, governance, network and cloud security, with hands-on labs, fair examinations and certificates anyone can verify.') }}</p>
                <div class="mt-8 flex flex-wrap gap-3">
                    <x-ui.button :href="lroute('programmes.index')" size="lg">{{ __('Explore Specializations') }} <x-icon name="arrow-right" /></x-ui.button>
                    <x-ui.button :href="lroute('corporate')" size="lg" variant="secondary"><x-icon name="building-2" /> {{ __('Corporate Training') }}</x-ui.button>
                </div>
                <p class="mt-6 text-sm text-subtle">{{ __('For learners in Nigeria, across Africa and internationally. Prices in Naira.') }}</p>
            </div>
            <x-ui.card class="overflow-hidden">
                <div class="flex items-center justify-between border-b border-border bg-elevated px-4 py-2.5 text-xs text-muted">
                    <span class="font-mono">{{ __('Lab 2 · KQL console') }}</span><span>{{ __('Simulation · synthetic data') }}</span>
                </div>
                <pre class="overflow-x-auto px-4 py-4 font-mono text-[13px] leading-relaxed text-text"><code><span class="text-blue">SigninLogs</span>
| <span class="text-accent">where</span> ResultType != <span class="text-warning">"0"</span>
| <span class="text-accent">summarize</span> Users = dcount(UserPrincipalName)
    <span class="text-accent">by</span> IPAddress
| <span class="text-accent">where</span> Users &gt;= 5</code></pre>
                <div class="border-t border-border px-4 py-3">
                    <table class="w-full font-mono text-xs">
                        <thead class="text-subtle"><tr><th class="pb-1 text-left font-normal">IPAddress</th><th class="pb-1 text-right font-normal">Users</th></tr></thead>
                        <tbody><tr><td>203.0.113.45</td><td class="text-right">12</td></tr></tbody>
                    </table>
                    <p class="mt-3 flex items-center gap-1.5 text-xs text-success"><x-icon name="badge-check" class="size-3.5" /> {{ __('Task validated on the server') }}</p>
                </div>
            </x-ui.card>
        </x-ui.container>
    </section>

    {{-- Why AMANIQ --}}
    <section class="py-16 sm:py-20">
        <x-ui.container>
            <x-ui.section-heading :eyebrow="__('Why AMANIQ Academy')" :title="__('Practical skills you can demonstrate')" :description="__('We focus on what you can do at the end of a programme, and on proving it fairly.')" />
            <div class="grid gap-5 sm:grid-cols-2">
                @foreach ($differentiators as [$icon, $title, $body])
                    <x-ui.card class="p-6">
                        <x-icon :name="$icon" class="size-6 text-accent" />
                        <h3 class="mt-4 font-semibold">{{ $title }}</h3>
                        <p class="mt-2 text-sm leading-relaxed text-muted">{{ $body }}</p>
                    </x-ui.card>
                @endforeach
            </div>
        </x-ui.container>
    </section>

    {{-- Programmes --}}
    <section class="border-y border-border bg-surface/50 py-16 sm:py-20">
        <x-ui.container>
            <div class="flex flex-wrap items-end justify-between gap-4">
                <x-ui.section-heading :eyebrow="__('Specializations')" :title="__('Five cybersecurity programmes')" :description="__('Programmes marked “in development” open for enrolment once their full curriculum and examination are published. Register your interest to hear first.')" />
                <x-ui.button :href="lroute('programmes.index')" variant="secondary" class="mb-10">{{ __('Compare all programmes') }}</x-ui.button>
            </div>
            <div class="grid gap-5 md:grid-cols-2 lg:grid-cols-3">
                @foreach ($programmes as $p)
                    <x-site.programme-card :p="$p" :show-outcomes="false" />
                @endforeach
            </div>
        </x-ui.container>
    </section>

    {{-- Labs --}}
    <section class="py-16 sm:py-20">
        <x-ui.container class="grid gap-10 lg:grid-cols-2 lg:items-center">
            <div>
                <x-ui.section-heading :eyebrow="__('Hands-on labs')" :title="__('Practise in safe, realistic simulations')" :description="__('Labs are part of the curriculum, not an add-on. Each one has a scenario, defined scope, tasks, progressive hints and server-side validation.')" />
                <ul class="space-y-3 text-sm text-muted">
                    @foreach ([__('Investigate synthetic Windows, Linux, proxy, DNS and cloud logs in an evidence explorer'), __('Write real KQL in a built-in query simulator: no Microsoft subscription required'), __('Build firewall policies that are tested against defined traffic flows'), __('Complete risk registers and control mappings scored against expert ranges'), __('Review IAM policies and investigate compromised cloud identities')] as $t)
                        <li class="flex gap-3"><x-icon name="server" class="mt-0.5 size-4 shrink-0 text-accent" />{{ $t }}</li>
                    @endforeach
                </ul>
            </div>
            <x-ui.card class="p-6">
                <h3 class="font-semibold">{{ __('Responsible by design') }}</h3>
                <p class="mt-2 text-sm leading-relaxed text-muted">{{ __('Every lab is clearly labelled as a simulation built on synthetic data. Nothing connects to real systems, and practical techniques may only be used in environments you are authorised to work in.') }}</p>
                <a href="{{ lroute('legal', 'acceptable-use') }}" class="mt-4 inline-flex items-center gap-1 text-sm font-semibold text-accent hover:underline">{{ __('Read the lab acceptable-use policy') }} <x-icon name="arrow-right" /></a>
            </x-ui.card>
        </x-ui.container>
    </section>

    {{-- How it works --}}
    <section class="border-y border-border bg-surface/50 py-16 sm:py-20">
        <x-ui.container>
            <x-ui.section-heading :eyebrow="__('How it works')" :title="__('From enrolment to verified credential')" align="center" />
            <ol class="grid gap-5 md:grid-cols-4">
                @foreach ($steps as $i => [$title, $body])
                    <li class="rounded-2xl border border-border bg-surface p-6">
                        <span class="flex size-9 items-center justify-center rounded-full bg-accent/15 font-semibold text-accent">{{ $i + 1 }}</span>
                        <h3 class="mt-4 font-semibold">{{ $title }}</h3>
                        <p class="mt-2 text-sm text-muted">{{ $body }}</p>
                    </li>
                @endforeach
            </ol>
        </x-ui.container>
    </section>

    {{-- Verification and corporate --}}
    <section class="py-16 sm:py-20">
        <x-ui.container class="grid gap-6 lg:grid-cols-2">
            <x-ui.card class="p-8">
                <x-icon name="qr-code" class="size-7 text-accent" />
                <h2 class="mt-4 text-2xl font-semibold">{{ __('Certificates employers can verify') }}</h2>
                <p class="mt-3 text-muted">{{ __('Every AMANIQ certificate has a unique, unguessable verification address and QR code. The verification page reads the live record, so it always shows whether the certificate is valid or revoked, without exposing private information.') }}</p>
                <x-ui.button :href="lroute('verify.index')" variant="secondary" class="mt-6">{{ __('Verify a certificate') }}</x-ui.button>
            </x-ui.card>
            <x-ui.card class="p-8">
                <x-icon name="building-2" class="size-7 text-accent" />
                <h2 class="mt-4 text-2xl font-semibold">{{ __('Training for your organisation') }}</h2>
                <p class="mt-3 text-muted">{{ __('Build security operations, governance and cloud skills across your team with structured programmes and progress visibility for managers.') }}</p>
                <x-ui.button :href="lroute('corporate')" variant="secondary" class="mt-6">{{ __('Explore corporate training') }}</x-ui.button>
            </x-ui.card>
        </x-ui.container>
    </section>

    @if ($articles->isNotEmpty())
        <section class="border-t border-border py-16 sm:py-20">
            <x-ui.container>
                <x-ui.section-heading :eyebrow="__('Learning resources')" :title="__('Latest from AMANIQ Academy')" />
                <div class="grid gap-5 md:grid-cols-3">
                    @foreach ($articles as $a)
                        <x-ui.card as="article" class="relative p-6 hover:border-border-strong">
                            <p class="text-xs text-subtle">{{ $a->published_at?->translatedFormat('j M Y') }}</p>
                            <h3 class="mt-2 font-semibold"><a href="{{ lroute('resources.show', $a) }}" class="after:absolute after:inset-0">{{ $a->title }}</a></h3>
                            <p class="mt-2 text-sm text-muted">{{ $a->summary }}</p>
                        </x-ui.card>
                    @endforeach
                </div>
            </x-ui.container>
        </section>
    @endif

    {{-- FAQ --}}
    <section class="border-t border-border py-16 sm:py-20">
        <x-ui.container class="max-w-3xl">
            <x-ui.section-heading :eyebrow="__('Questions')" :title="__('Frequently asked questions')" />
            <div class="divide-y divide-border rounded-2xl border border-border">
                @foreach ($faqs as [$q, $a])
                    <details class="group p-5">
                        <summary class="flex cursor-pointer list-none items-center justify-between gap-4 font-medium [&::-webkit-details-marker]:hidden">{{ $q }}<span aria-hidden="true" class="text-subtle transition-transform group-open:rotate-45">+</span></summary>
                        <p class="mt-3 text-sm leading-relaxed text-muted">{{ $a }}</p>
                    </details>
                @endforeach
            </div>
            <div class="mt-10 flex flex-col items-center gap-3 text-center">
                <x-icon name="graduation-cap" class="size-7 text-accent" />
                <p class="text-lg font-semibold">{{ __('Ready to start?') }}</p>
                <x-ui.button :href="lroute('programmes.show', 'soc-analyst')">{{ __('View the SOC Analyst programme') }}</x-ui.button>
            </div>
        </x-ui.container>
    </section>
</x-layouts.site>
