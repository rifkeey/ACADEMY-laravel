<x-layouts.auth title="Choose a new password">
    <x-slot:intro>Use at least 10 characters.</x-slot:intro>
    <form method="POST" action="{{ route('password.update') }}" class="space-y-5" novalidate>
        @csrf
        <input type="hidden" name="token" value="{{ $request->route('token') }}">
        <x-ui.field label="Email" for="email" required><x-ui.input name="email" type="email" :value="$request->email" autocomplete="email" required /></x-ui.field>
        <x-ui.field label="New password" for="password" required><x-ui.input name="password" type="password" autocomplete="new-password" required /></x-ui.field>
        <x-ui.field label="Confirm new password" for="password_confirmation" required><x-ui.input name="password_confirmation" type="password" autocomplete="new-password" required /></x-ui.field>
        <x-ui.button class="w-full">Update password</x-ui.button>
    </form>
</x-layouts.auth>
