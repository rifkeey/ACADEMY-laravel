<x-layouts.site :title="__('About AMANIQ Academy')" :description="__('AMANIQ Academy is the technology education division of AMANIQ Projects Limited.')">
    @php
        $principles = [
            __('Trust') => __('We describe our programmes, credentials and outcomes accurately, and never claim accreditation or partnerships we do not have.'),
            __('Practical learning') => __('Every programme is built around doing: investigations, queries, policies and reports.'),
            __('Integrity') => __('Assessments are graded on the server, requirements are the same for everyone, and certificates can be revoked when warranted.'),
            __('Professionalism') => __('We teach the documentation, communication and ethics that employers expect.'),
            __('Security') => __('We treat the security of our platform and your data as a core product requirement.'),
            __('Continuous improvement') => __('Content carries review dates and a source register, and course versions keep your records stable as material is updated.'),
        ];
    @endphp
    <x-ui.container class="py-14">
        <x-ui.page-header :eyebrow="__('About')" :title="__('About AMANIQ Academy')" :description="__('Practical, accessible and professionally structured technology education, with a focus on cybersecurity, hands-on skills and workplace readiness.')" />
        <div class="mt-10 grid gap-10 lg:grid-cols-[1.4fr_1fr]">
            <div class="space-y-5 leading-relaxed text-muted">
                <p>{!! __('AMANIQ Academy is a business division of :company. It is not a separate legal entity. We serve learners in Nigeria, across Africa and internationally, and organisations that want to develop their teams.', ['company' => '<a href="https://amaniqgroup.com" class="text-text underline">AMANIQ Projects Limited</a>']) !!}</p>
                <p>{{ __('Our programmes combine original teaching with references to authoritative frameworks and documentation, simulated labs that validate what you do, and fair examinations. Certificates are issued by AMANIQ Academy and confirm completion of our programmes. They are not government-accredited qualifications or vendor certifications.') }}</p>
                <div class="rounded-xl border border-dashed border-border-strong p-4 text-sm">
                    <p class="font-semibold text-text">{{ __('[Placeholder: team and instructors]') }}</p>
                    <p class="mt-1">{{ __('Add instructor profiles and qualifications here once they have been confirmed. We do not publish credentials that have not been verified.') }}</p>
                </div>
            </div>
            <x-ui.card class="p-6">
                <h2 class="font-semibold">{{ __('Our principles') }}</h2>
                <dl class="mt-4 space-y-4 text-sm">@foreach ($principles as $k => $v)<div><dt class="font-medium">{{ $k }}</dt><dd class="mt-0.5 text-muted">{{ $v }}</dd></div>@endforeach</dl>
            </x-ui.card>
        </div>
        <div class="mt-12 flex gap-3">
            <x-ui.button :href="lroute('programmes.index')">{{ __('Explore programmes') }}</x-ui.button>
            <x-ui.button :href="lroute('contact')" variant="secondary">{{ __('Contact us') }}</x-ui.button>
        </div>
    </x-ui.container>
</x-layouts.site>
