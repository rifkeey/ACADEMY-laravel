<x-layouts.site :title="__('Contact and training enquiries')">
    <x-ui.container class="py-14">
        <x-ui.page-header :eyebrow="__('Contact')" :title="__('Contact AMANIQ Academy')" :description="__('Questions about programmes, enrolment, corporate training or certificates.')" />
        <div class="mt-10 grid gap-10 lg:grid-cols-[1fr_1.4fr]">
            <div class="space-y-4 text-sm text-muted">
                <p><span class="font-semibold text-text">{{ __('Email:') }}</span> {{ $contactEmail ?: __('[Placeholder: contact email to be confirmed]') }}</p>
                <p><span class="font-semibold text-text">{{ __('Support hours:') }}</span> {{ $supportHours ?: __('[Placeholder]') }}</p>
                <p><span class="font-semibold text-text">{{ __('Parent company:') }}</span> <a class="underline" href="https://amaniqgroup.com">{{ __('AMANIQ Projects Limited') }}</a></p>
            </div>
            <x-ui.card class="p-6"><x-site.enquiry-form :kind="$kind" :programme="$programme" :programmes="$programmes" /></x-ui.card>
        </div>
    </x-ui.container>
</x-layouts.site>
