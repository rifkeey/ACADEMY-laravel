<x-layouts.site title="Account" :noindex="true">
    <x-ui.container class="py-12">
        <x-ui.page-header eyebrow="Account" title="Your account" />
        <div class="mt-8 grid gap-6 lg:grid-cols-2">
            <x-ui.card class="p-6">
                <h2 class="font-semibold">Profile</h2>
                @if (session('status') === 'profile-information-updated')
                    <x-ui.alert tone="success" class="mt-4">Profile updated. Certificates already issued keep the name they were issued with.</x-ui.alert>
                @endif
                {{-- Fortify endpoint; changing the email address requires confirming the new one. --}}
                <form method="POST" action="{{ route('user-profile-information.update') }}" class="mt-5 space-y-5">
                    @csrf @method('PUT')
                    <x-ui.field label="Full name" for="name" hint="Used on certificates issued from now on." required><x-ui.input name="name" :value="$user->name" autocomplete="name" required maxlength="120" /></x-ui.field>
                    <x-ui.field label="Email" for="email" required><x-ui.input name="email" type="email" :value="$user->email" autocomplete="email" required /></x-ui.field>
                    <x-ui.button>Save profile</x-ui.button>
                </form>
            </x-ui.card>
            <x-ui.card class="p-6">
                <h2 class="font-semibold">Password</h2>
                @if (session('status') === 'password-updated')<x-ui.alert tone="success" class="mt-4">Password updated.</x-ui.alert>@endif
                <form method="POST" action="{{ route('user-password.update') }}" class="mt-5 space-y-5">
                    @csrf @method('PUT')
                    <x-ui.field label="Current password" for="current_password" required><x-ui.input name="current_password" type="password" autocomplete="current-password" required /></x-ui.field>
                    <x-ui.field label="New password" for="password" required><x-ui.input name="password" type="password" autocomplete="new-password" required /></x-ui.field>
                    <x-ui.field label="Confirm new password" for="password_confirmation" required><x-ui.input name="password_confirmation" type="password" autocomplete="new-password" required /></x-ui.field>
                    <x-ui.button>Update password</x-ui.button>
                </form>
                <div class="mt-6 space-y-2 border-t border-border pt-5 text-sm text-muted">
                    <p><span class="text-text">Role:</span> <x-ui.pill>{{ $user->role }}</x-ui.pill></p>
                    <p>Two-factor authentication: <strong class="text-text">{{ $user->hasEnabledTwoFactorAuthentication() ? 'on' : 'off' }}</strong> · <a href="{{ route('account.security') }}" class="text-accent underline">Manage</a></p>
                    <p>To request a copy or deletion of your data, see the <a href="{{ route('legal', 'privacy') }}" class="text-text underline">privacy policy</a>.</p>
                </div>
            </x-ui.card>
        </div>
    </x-ui.container>
</x-layouts.site>
