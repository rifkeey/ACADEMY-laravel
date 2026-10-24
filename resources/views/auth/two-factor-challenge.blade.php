<x-layouts.auth :title="__('Two-factor authentication')">
    <x-slot:intro>{{ __('Enter the 6-digit code from your authenticator app, or one of your recovery codes.') }}</x-slot:intro>
    <form method="POST" action="{{ route('two-factor.login') }}" class="space-y-5" novalidate>
        @csrf
        <x-ui.field :label="__('Authentication code')" for="code"><x-ui.input name="code" inputmode="numeric" autocomplete="one-time-code" autofocus /></x-ui.field>
        <p class="text-center text-xs text-subtle">{{ __('or') }}</p>
        <x-ui.field :label="__('Recovery code')" for="recovery_code"><x-ui.input name="recovery_code" autocomplete="off" /></x-ui.field>
        <x-ui.button class="w-full">{{ __('Continue') }}</x-ui.button>
    </form>
</x-layouts.auth>
