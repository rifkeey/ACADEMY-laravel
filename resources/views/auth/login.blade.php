<x-layouts.auth :title="__('Sign in')">
    <x-slot:intro>{{ __('Continue your learning with AMANIQ Academy.') }}</x-slot:intro>
    @if (session('status'))<x-ui.alert tone="success" class="mb-5">{{ session('status') }}</x-ui.alert>@endif
    <form method="POST" action="{{ route('login') }}" class="space-y-5" novalidate>
        @csrf
        <x-ui.field :label="__('Email')" for="email" required><x-ui.input name="email" type="email" autocomplete="email" required autofocus /></x-ui.field>
        <x-ui.field :label="__('Password')" for="password" required><x-ui.input name="password" type="password" autocomplete="current-password" required /></x-ui.field>
        <div class="flex items-center justify-between text-sm">
            <label class="flex items-center gap-2 text-muted"><input type="checkbox" name="remember" class="size-4 accent-[var(--accent)]"> {{ __('Keep me signed in') }}</label>
            <a href="{{ route('password.request') }}" class="text-muted underline-offset-2 hover:text-text hover:underline">{{ __('Forgot your password?') }}</a>
        </div>
        <x-ui.button class="w-full">{{ __('Sign in') }}</x-ui.button>
    </form>
    <p class="mt-6 text-center text-sm text-muted">{{ __('New to AMANIQ Academy?') }} <a href="{{ route('register') }}" class="font-semibold text-accent hover:underline">{{ __('Create an account') }}</a></p>
</x-layouts.auth>
