<x-layouts.site title="Corporate cybersecurity training" description="Train your SOC, IT, risk and cloud teams with AMANIQ Academy programmes and hands-on labs.">
    <x-ui.container class="py-14">
        <x-ui.page-header eyebrow="For organisations" title="Corporate training" description="Structured cybersecurity skills development for security operations, IT, governance and cloud teams." />
        <div class="mt-10 grid gap-5 md:grid-cols-3">
            @foreach ([['users', 'Team enrolment', 'Enrol staff on the same programme versions, with access managed by AMANIQ administrators.'], ['clipboard-list', 'Practical assessment', 'Labs and examinations are validated on the server, so completion reflects demonstrated work.'], ['bar-chart-3', 'Progress reporting', 'Agree progress and completion reporting for your organisation as part of a training arrangement.']] as [$icon, $title, $body])
                <x-ui.card class="p-6"><x-icon :name="$icon" class="size-6 text-accent" /><h2 class="mt-3 font-semibold">{{ $title }}</h2><p class="mt-2 text-sm text-muted">{{ $body }}</p></x-ui.card>
            @endforeach
        </div>
        <p class="mt-6 text-sm text-subtle">Corporate packages and pricing are agreed individually. Self-service team checkout is planned for a later release.</p>
        <div class="mt-12 grid gap-10 lg:grid-cols-[1fr_1.3fr]">
            <div>
                <h2 class="text-2xl font-semibold">Tell us about your team</h2>
                <p class="mt-3 text-muted">Share the roles you want to develop, your team size and your timeline. We will reply by email.</p>
            </div>
            <x-ui.card class="p-6"><x-site.enquiry-form kind="corporate" :programmes="$programmes" /></x-ui.card>
        </div>
    </x-ui.container>
</x-layouts.site>
