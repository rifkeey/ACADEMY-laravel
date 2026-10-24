<x-layouts.site :title="__('Two-factor authentication')" :noindex="true">
    <x-ui.container class="py-12">
        <x-ui.page-header :eyebrow="__('Account security')" :title="__('Two-factor authentication')" :description="__('Protect your account with a 6-digit code from an authenticator app (for example Google Authenticator, Microsoft Authenticator or 1Password). Required for administrators.')" />
        <x-ui.card class="mt-8 max-w-2xl space-y-5 p-6">
            @if (! $user->two_factor_secret)
                <p class="text-sm text-muted">{{ __('Two-factor authentication is off.') }}</p>
                <form method="POST" action="{{ route('two-factor.enable') }}">@csrf<x-ui.button>{{ __('Turn on two-factor authentication') }}</x-ui.button></form>
            @elseif (! $user->two_factor_confirmed_at)
                <p class="text-sm text-muted">{{ __('Scan this QR code with your authenticator app, then enter the 6-digit code to finish.') }}</p>
                <div class="inline-block rounded-xl bg-white p-3">{!! $user->twoFactorQrCodeSvg() !!}</div>
                <form method="POST" action="{{ route('two-factor.confirm') }}" class="flex max-w-sm items-end gap-2">
                    @csrf
                    <x-ui.field :label="__('Code')" for="code"><x-ui.input name="code" inputmode="numeric" autocomplete="one-time-code" required /></x-ui.field>
                    <x-ui.button>{{ __('Confirm') }}</x-ui.button>
                </form>
                @error('code', 'confirmTwoFactorAuthentication')<p class="text-xs text-danger">{{ $message }}</p>@enderror
            @else
                <x-ui.alert tone="success" :title="__('Two-factor authentication is on')" />
                <div>
                    <p class="text-sm font-semibold">{{ __('Recovery codes') }}</p>
                    <p class="mt-1 text-sm text-muted">{{ __('Store these somewhere safe. Each can be used once if you lose your device.') }}</p>
                    <ul class="mt-3 grid grid-cols-2 gap-2 rounded-xl border border-border bg-bg p-4 font-mono text-sm">
                        @foreach ($user->recoveryCodes() as $code)<li>{{ $code }}</li>@endforeach
                    </ul>
                </div>
                <div class="flex flex-wrap gap-2">
                    <form method="POST" action="{{ route('two-factor.regenerate-recovery-codes') }}">@csrf<x-ui.button variant="secondary" size="sm">{{ __('Regenerate recovery codes') }}</x-ui.button></form>
                    <form method="POST" action="{{ route('two-factor.disable') }}">@csrf @method('DELETE')<x-ui.button variant="danger" size="sm">{{ __('Turn off') }}</x-ui.button></form>
                </div>
            @endif
        </x-ui.card>
    </x-ui.container>
</x-layouts.site>
