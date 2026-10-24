@php
    $columns = [
        __('Learn') => [[lroute('programmes.index'), __('All programmes')], [lroute('programmes.show', 'soc-analyst'), __('SOC Analyst')], [lroute('resources.index'), __('Learning resources')], [lroute('verify.index'), __('Verify a certificate')]],
        __('Organisations') => [[lroute('corporate'), __('Corporate training')], [lroute('contact'), __('Contact and enquiries')]],
        __('Policies') => [[lroute('legal', 'privacy'), __('Privacy policy')], [lroute('legal', 'terms'), __('Terms of service')], [lroute('legal', 'acceptable-use'), __('Acceptable use for labs')], [lroute('legal', 'refunds'), __('Refunds and cancellations')]],
    ];
@endphp
<footer class="mt-24 border-t border-border bg-surface">
    <x-ui.container class="grid gap-10 py-14 md:grid-cols-[1.4fr_repeat(3,1fr)]">
        <div class="max-w-xs">
            <x-brand.lockup division="Academy" size="sm" class="text-text" />
            <p class="mt-4 text-sm leading-relaxed text-muted">
                {!! __('Practical, professionally structured technology education. AMANIQ Academy is a business division of :company.', ['company' => '<a href="https://amaniqgroup.com" class="text-text underline underline-offset-2">AMANIQ Projects Limited</a>']) !!}
            </p>
        </div>
        @foreach ($columns as $heading => $links)
            <div>
                <h2 class="text-sm font-semibold">{{ $heading }}</h2>
                <ul class="mt-3 space-y-2">
                    @foreach ($links as [$href, $label])
                        <li><a href="{{ $href }}" class="text-sm text-muted hover:text-text">{{ $label }}</a></li>
                    @endforeach
                </ul>
            </div>
        @endforeach
    </x-ui.container>
    <x-ui.container class="flex flex-col gap-2 border-t border-border py-6 text-xs text-subtle sm:flex-row sm:justify-between">
        <p>{{ __('© :year AMANIQ Digital Labs. All rights reserved.', ['year' => now()->year]) }}</p>
        <p>{{ __('AMANIQ certificates confirm course completion. They are not government-accredited qualifications or vendor certifications.') }}</p>
    </x-ui.container>
</footer>
