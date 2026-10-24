<x-layouts.admin :title="__('Certificates')" :description="__('Issued certificates. Revoking one changes its public verification page to “revoked” immediately; every change needs a reason and is recorded.')">
    <form method="GET" class="mb-6 flex flex-wrap items-end gap-3">
        <div class="w-72"><x-ui.field :label="__('Certificate number, credential ID or name')" for="q"><x-ui.input type="search" name="q" :value="$q" /></x-ui.field></div>
        <div class="w-40"><x-ui.field :label="__('Status')" for="status"><x-ui.select name="status"><option value="">{{ __('Any') }}</option><option value="valid" @selected($status === 'valid')>{{ __('Valid') }}</option><option value="revoked" @selected($status === 'revoked')>{{ __('Revoked') }}</option></x-ui.select></x-ui.field></div>
        <x-ui.button variant="secondary">{{ __('Filter') }}</x-ui.button>
    </form>

    <x-ui.table :head="[__('Certificate'), __('Holder'), __('Programme'), __('Issued'), __('Status'), '']" :caption="__('Certificates')">
        @forelse ($certificates as $c)
            <tr>
                <x-ui.td><p class="font-mono text-xs">{{ $c->certificate_number }}</p><a href="{{ lroute('verify.show', $c->credential_id) }}" class="font-mono text-xs text-accent-strong hover:underline">{{ $c->credential_id }}</a></x-ui.td>
                <x-ui.td><p>{{ $c->holder_name }}</p><p class="text-xs text-muted">{{ $c->user->email }}</p></x-ui.td>
                <x-ui.td>{{ $c->programme_title }} <span class="text-xs text-subtle">v{{ $c->course_version }}</span></x-ui.td>
                <x-ui.td class="whitespace-nowrap">{{ $c->issued_at->translatedFormat('j M Y') }}</x-ui.td>
                <x-ui.td>@include('admin.partials.status', ['value' => $c->status])@if ($c->revocation_reason)<p class="mt-1 max-w-48 text-xs text-muted">{{ $c->revocation_reason }}</p>@endif</x-ui.td>
                <x-ui.td class="space-y-2">
                    <a href="{{ route('certificates.pdf', $c) }}" class="block text-sm text-accent-strong hover:underline">{{ __('PDF') }}</a>
                    @if ($c->status === 'valid')
                        <x-ui.confirm :action="route('admin.certificates.revoke', $c)" :label="__('Revoke')" title="Revoke {{ $c->certificate_number }}?" :description="__('Anyone checking this certificate will see that it was revoked. You can reinstate it later.')" :confirm-label="__('Revoke certificate')" :require-reason="true" />
                    @else
                        <x-ui.confirm :action="route('admin.certificates.reinstate', $c)" :label="__('Reinstate')" variant="secondary" title="Reinstate {{ $c->certificate_number }}?" :description="__('The verification page will show it as valid again.')" :confirm-label="__('Reinstate')" :require-reason="true" />
                    @endif
                </x-ui.td>
            </tr>
        @empty
            <tr><x-ui.td colspan="6" class="text-center text-muted">{{ __('No certificates found.') }}</x-ui.td></tr>
        @endforelse
    </x-ui.table>
    <x-admin.pager :items="$certificates" />
</x-layouts.admin>
