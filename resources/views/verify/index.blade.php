<x-layouts.site title="Verify a certificate" description="Check whether an AMANIQ Academy certificate is valid.">
    <x-ui.container class="py-14">
        <x-ui.page-header eyebrow="Credential verification" title="Verify an AMANIQ Academy certificate" description="Scan the QR code on the certificate, or enter the verification ID printed beneath it." />
        <div class="mt-10 grid gap-8 lg:grid-cols-[1.2fr_1fr]">
            <x-ui.card class="p-6">
                <form method="POST" action="{{ route('verify.lookup') }}" class="space-y-4">
                    @csrf
                    <x-ui.field label="Verification ID" for="id" hint="26 characters, letters and numbers, shown at the end of the verification link.">
                        <x-ui.input name="id" required maxlength="40" autocomplete="off" spellcheck="false" class="font-mono uppercase" />
                    </x-ui.field>
                    <x-ui.button>Verify</x-ui.button>
                </form>
            </x-ui.card>
            <div class="space-y-3 text-sm text-muted">
                <x-icon name="shield-check" class="size-6 text-accent" />
                <p>Verification reads the live certificate record. A PDF or QR image on its own is not proof: always confirm the result on this website.</p>
                <p>The verification page shows the holder’s name, programme, issue date, certificate number and status. It never shows email addresses, account details or examination answers.</p>
            </div>
        </div>
    </x-ui.container>
</x-layouts.site>
