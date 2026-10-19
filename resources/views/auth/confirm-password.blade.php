<x-layouts.auth title="Confirm your password">
    <x-slot:intro>For your security, please confirm your password to continue.</x-slot:intro>
    <form method="POST" action="{{ route('password.confirm') }}" class="space-y-5" novalidate>
        @csrf
        <x-ui.field label="Password" for="password" required><x-ui.input name="password" type="password" autocomplete="current-password" required autofocus /></x-ui.field>
        <x-ui.button class="w-full">Confirm</x-ui.button>
    </form>
</x-layouts.auth>
