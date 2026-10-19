<x-layouts.auth title="Create your account">
    <x-slot:intro>Free to create. You only pay when you enrol in a programme.</x-slot:intro>
    <form method="POST" action="{{ route('register') }}" class="space-y-5" novalidate>
        @csrf
        <x-ui.field label="Full name" for="name" hint="As it should appear on your certificates." required><x-ui.input name="name" autocomplete="name" required maxlength="120" /></x-ui.field>
        <x-ui.field label="Email" for="email" required><x-ui.input name="email" type="email" autocomplete="email" required /></x-ui.field>
        <x-ui.field label="Password" for="password" hint="At least 10 characters. A passphrase works well. Passwords found in known data breaches are refused." required>
            <x-ui.input name="password" type="password" autocomplete="new-password" minlength="10" required />
        </x-ui.field>
        <x-ui.field label="Confirm password" for="password_confirmation" required><x-ui.input name="password_confirmation" type="password" autocomplete="new-password" required /></x-ui.field>
        <div>
            <label class="flex items-start gap-3 text-sm text-muted">
                <input type="checkbox" name="terms" value="1" class="mt-1 size-4 accent-[var(--accent)]" @checked(old('terms')) @error('terms') aria-invalid="true" aria-describedby="terms-error" @enderror>
                <span>I agree to the <a href="{{ route('legal', 'terms') }}" class="text-text underline">terms of service</a> and the <a href="{{ route('legal', 'acceptable-use') }}" class="text-text underline">acceptable-use policy for labs</a>, and I have read the <a href="{{ route('legal', 'privacy') }}" class="text-text underline">privacy policy</a>.</span>
            </label>
            @error('terms')<p id="terms-error" class="mt-1 text-xs text-danger">{{ $message }}</p>@enderror
        </div>
        <x-ui.button class="w-full">Create account</x-ui.button>
    </form>
    <p class="mt-6 text-center text-sm text-muted">Already have an account? <a href="{{ route('login') }}" class="font-semibold text-accent hover:underline">Sign in</a></p>
</x-layouts.auth>
