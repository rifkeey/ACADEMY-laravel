<x-layouts.site :title="__('Certificate verification')" :noindex="true">
    <x-ui.container class="py-14">
        <div class="mx-auto max-w-2xl">
            <p class="text-sm font-semibold uppercase tracking-[0.14em] text-accent">{{ __('Credential verification') }}</p>
            @if ($r['status'] === 'not_found')
                <x-ui.card class="mt-4 p-8">
                    <div class="flex items-center gap-3"><x-icon name="file-question" class="size-8 text-warning" /><h1 class="text-2xl font-semibold">{{ __('Certificate not found') }}</h1></div>
                    <p class="mt-4 text-muted">{{ __('No AMANIQ Academy certificate matches this verification ID. Check that the ID was entered exactly as printed, or scan the QR code on the certificate.') }}</p>
                    <p class="mt-2 text-sm text-subtle">{{ __('If you believe this is an error, contact AMANIQ Academy.') }}</p>
                </x-ui.card>
            @else
                @php $valid = $r['status'] === 'valid'; @endphp
                <x-ui.card class="mt-4 overflow-hidden {{ $valid ? 'border-success/50' : 'border-danger/50' }}">
                    <div class="flex items-center gap-3 px-8 py-5 {{ $valid ? 'bg-success/10' : 'bg-danger/10' }}">
                        <x-icon :name="$valid ? 'check-circle-2' : 'x-circle'" class="size-8 {{ $valid ? 'text-success' : 'text-danger' }}" />
                        <div>
                            <h1 class="text-2xl font-semibold">{{ $valid ? __('Valid certificate') : __('Revoked certificate') }}</h1>
                            <p class="text-sm text-muted">{{ $valid ? __('This certificate was issued by AMANIQ Academy and is currently valid.') : __('This certificate was revoked on :date and is no longer valid.', ['date' => $r['revokedAt']->translatedFormat('j M Y')]) }}</p>
                        </div>
                    </div>
                    <dl class="grid gap-5 p-8 sm:grid-cols-2">
                        @foreach ([__('Certificate holder') => $r['holderName'], __('Programme') => $r['programmeTitle'], __('Issue date') => $r['issuedAt']->translatedFormat('j M Y'), __('Certificate number') => $r['certificateNumber'], __('Course version') => $r['courseVersion'], __('Issuing organisation') => $r['issuer']] as $k => $v)
                            <div><dt class="text-xs uppercase tracking-wide text-subtle">{{ $k }}</dt><dd class="mt-1 font-medium">{{ $v }}</dd></div>
                        @endforeach
                    </dl>
                    <p class="border-t border-border px-8 py-4 text-xs text-subtle">{{ __('AMANIQ Academy certificates confirm completion of an AMANIQ training programme. They are not government-accredited qualifications or vendor certifications.') }}</p>
                </x-ui.card>
            @endif
            <p class="mt-6 text-sm"><a href="{{ lroute('verify.index') }}" class="text-muted hover:text-text">{{ __('Verify another certificate') }}</a></p>
        </div>
    </x-ui.container>
</x-layouts.site>
