<x-layouts.auth :title="__('Confirm your email')">
    <x-slot:intro>{{ __('We sent a confirmation link to your email address. Open it to activate your account.') }}</x-slot:intro>
    @if (session('status') === 'verification-link-sent')
        <x-ui.alert tone="success" class="mb-5">{{ __('A new confirmation link has been sent.') }}</x-ui.alert>
    @endif
    <div class="flex flex-wrap items-center justify-between gap-3">
        <form method="POST" action="{{ route('verification.send') }}">@csrf<x-ui.button>{{ __('Resend the link') }}</x-ui.button></form>
        <form method="POST" action="{{ route('logout') }}">@csrf<x-ui.button variant="ghost">{{ __('Sign out') }}</x-ui.button></form>
    </div>
</x-layouts.auth>
