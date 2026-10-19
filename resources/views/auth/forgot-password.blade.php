<x-layouts.auth title="Reset your password">
    <x-slot:intro>Enter your email and we will send you a secure reset link.</x-slot:intro>
    {{-- Fortify returns the same message whether or not the email exists. --}}
    @if (session('status'))<x-ui.alert tone="success" class="mb-5">{{ session('status') }}</x-ui.alert>@endif
    <form method="POST" action="{{ route('password.email') }}" class="space-y-5" novalidate>
        @csrf
        <x-ui.field label="Email" for="email" required><x-ui.input name="email" type="email" autocomplete="email" required /></x-ui.field>
        <x-ui.button class="w-full">Send reset link</x-ui.button>
    </form>
    <p class="mt-6 text-center text-sm"><a href="{{ route('login') }}" class="text-muted hover:text-text">Back to sign in</a></p>
</x-layouts.auth>
