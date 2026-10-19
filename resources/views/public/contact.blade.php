<x-layouts.site title="Contact and training enquiries">
    <x-ui.container class="py-14">
        <x-ui.page-header eyebrow="Contact" title="Contact AMANIQ Academy" description="Questions about programmes, enrolment, corporate training or certificates." />
        <div class="mt-10 grid gap-10 lg:grid-cols-[1fr_1.4fr]">
            <div class="space-y-4 text-sm text-muted">
                <p><span class="font-semibold text-text">Email:</span> {{ $contactEmail ?: '[Placeholder: contact email to be confirmed]' }}</p>
                <p><span class="font-semibold text-text">Support hours:</span> {{ $supportHours ?: '[Placeholder]' }}</p>
                <p><span class="font-semibold text-text">Parent company:</span> <a class="underline" href="https://amaniqgroup.com">AMANIQ Projects Limited</a></p>
            </div>
            <x-ui.card class="p-6"><x-site.enquiry-form :kind="$kind" :programme="$programme" :programmes="$programmes" /></x-ui.card>
        </div>
    </x-ui.container>
</x-layouts.site>
