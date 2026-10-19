<x-layouts.site title="About AMANIQ Academy" description="AMANIQ Academy is the technology education division of AMANIQ Projects Limited.">
    @php
        $principles = [
            'Trust' => 'We describe our programmes, credentials and outcomes accurately, and never claim accreditation or partnerships we do not have.',
            'Practical learning' => 'Every programme is built around doing: investigations, queries, policies and reports.',
            'Integrity' => 'Assessments are graded on the server, requirements are the same for everyone, and certificates can be revoked when warranted.',
            'Professionalism' => 'We teach the documentation, communication and ethics that employers expect.',
            'Security' => 'We treat the security of our platform and your data as a core product requirement.',
            'Continuous improvement' => 'Content carries review dates and a source register, and course versions keep your records stable as material is updated.',
        ];
    @endphp
    <x-ui.container class="py-14">
        <x-ui.page-header eyebrow="About" title="About AMANIQ Academy" description="Practical, accessible and professionally structured technology education, with a focus on cybersecurity, hands-on skills and workplace readiness." />
        <div class="mt-10 grid gap-10 lg:grid-cols-[1.4fr_1fr]">
            <div class="space-y-5 leading-relaxed text-muted">
                <p>AMANIQ Academy is a business division of <a href="https://amaniqgroup.com" class="text-text underline">AMANIQ Projects Limited</a>. It is not a separate legal entity. We serve learners in Nigeria, across Africa and internationally, and organisations that want to develop their teams.</p>
                <p>Our programmes combine original teaching with references to authoritative frameworks and documentation, simulated labs that validate what you do, and fair examinations. Certificates are issued by AMANIQ Academy and confirm completion of our programmes. They are not government-accredited qualifications or vendor certifications.</p>
                <div class="rounded-xl border border-dashed border-border-strong p-4 text-sm">
                    <p class="font-semibold text-text">[Placeholder: team and instructors]</p>
                    <p class="mt-1">Add instructor profiles and qualifications here once they have been confirmed. We do not publish credentials that have not been verified.</p>
                </div>
            </div>
            <x-ui.card class="p-6">
                <h2 class="font-semibold">Our principles</h2>
                <dl class="mt-4 space-y-4 text-sm">@foreach ($principles as $k => $v)<div><dt class="font-medium">{{ $k }}</dt><dd class="mt-0.5 text-muted">{{ $v }}</dd></div>@endforeach</dl>
            </x-ui.card>
        </div>
        <div class="mt-12 flex gap-3">
            <x-ui.button :href="route('programmes.index')">Explore programmes</x-ui.button>
            <x-ui.button :href="route('contact')" variant="secondary">Contact us</x-ui.button>
        </div>
    </x-ui.container>
</x-layouts.site>
