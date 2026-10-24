<x-layouts.auth :title="__('Create your account')">
    <x-slot:intro>{{ __('Free to create. You only pay when you enrol in a programme.') }}</x-slot:intro>
    <form method="POST" action="{{ route('register') }}" class="space-y-5" novalidate>
        @csrf
        <x-ui.field :label="__('Full name')" for="name" :hint="__('As it should appear on your certificates.')" required><x-ui.input name="name" autocomplete="name" required maxlength="120" /></x-ui.field>
        <x-ui.field :label="__('Email')" for="email" required><x-ui.input name="email" type="email" autocomplete="email" required /></x-ui.field>
        <x-ui.field :label="__('Password')" for="password" :hint="__('At least 10 characters. A passphrase works well. Passwords found in known data breaches are refused.')" required>
            <x-ui.input name="password" type="password" autocomplete="new-password" minlength="10" required />
        </x-ui.field>
        <x-ui.field :label="__('Confirm password')" for="password_confirmation" required><x-ui.input name="password_confirmation" type="password" autocomplete="new-password" required /></x-ui.field>
        <div>
            <label class="flex items-start gap-3 text-sm text-muted">
                <input type="checkbox" name="terms" value="1" class="mt-1 size-4 accent-[var(--accent)]" @checked(old('terms')) @error('terms') aria-invalid="true" aria-describedby="terms-error" @enderror>
                <span>{!! __('I agree to the :terms and the :aup, and I have read the :privacy.', [
                    'terms' => '<a href="'.e(lroute('legal', 'terms')).'" class="text-text underline">'.e(__('terms of service')).'</a>',
                    'aup' => '<a href="'.e(lroute('legal', 'acceptable-use')).'" class="text-text underline">'.e(__('acceptable-use policy for labs')).'</a>',
                    'privacy' => '<a href="'.e(lroute('legal', 'privacy')).'" class="text-text underline">'.e(__('privacy policy')).'</a>',
                ]) !!}</span>
            </label>
            @error('terms')<p id="terms-error" class="mt-1 text-xs text-danger">{{ $message }}</p>@enderror
        </div>
        <x-ui.button class="w-full">{{ __('Create account') }}</x-ui.button>
    </form>
    <p class="mt-6 text-center text-sm text-muted">{{ __('Already have an account?') }} <a href="{{ route('login') }}" class="font-semibold text-accent hover:underline">{{ __('Sign in') }}</a></p>
</x-layouts.auth>
