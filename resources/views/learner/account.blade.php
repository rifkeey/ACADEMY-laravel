<x-layouts.site :title="__('Account')" :noindex="true">
    <x-ui.container class="py-12">
        <x-ui.page-header :eyebrow="__('Account')" :title="__('Your account')" />
        <div class="mt-8 grid gap-6 lg:grid-cols-2">
            <x-ui.card class="p-6">
                <h2 class="font-semibold">{{ __('Profile') }}</h2>
                @if (session('status') === 'profile-information-updated')
                    <x-ui.alert tone="success" class="mt-4">{{ __('Profile updated. Certificates already issued keep the name they were issued with.') }}</x-ui.alert>
                @endif
                {{-- Fortify endpoint; changing the email address requires confirming the new one. --}}
                <form method="POST" action="{{ route('user-profile-information.update') }}" class="mt-5 space-y-5">
                    @csrf @method('PUT')
                    <x-ui.field :label="__('Full name')" for="name" :hint="__('Used on certificates issued from now on.')" required><x-ui.input name="name" :value="$user->name" autocomplete="name" required maxlength="120" /></x-ui.field>
                    <x-ui.field :label="__('Email')" for="email" required><x-ui.input name="email" type="email" :value="$user->email" autocomplete="email" required /></x-ui.field>
                    <x-ui.button>{{ __('Save profile') }}</x-ui.button>
                </form>
            </x-ui.card>
            <x-ui.card class="p-6">
                <h2 class="font-semibold">{{ __('Password') }}</h2>
                @if (session('status') === 'password-updated')<x-ui.alert tone="success" class="mt-4">{{ __('Password updated.') }}</x-ui.alert>@endif
                <form method="POST" action="{{ route('user-password.update') }}" class="mt-5 space-y-5">
                    @csrf @method('PUT')
                    <x-ui.field :label="__('Current password')" for="current_password" required><x-ui.input name="current_password" type="password" autocomplete="current-password" required /></x-ui.field>
                    <x-ui.field :label="__('New password')" for="password" required><x-ui.input name="password" type="password" autocomplete="new-password" required /></x-ui.field>
                    <x-ui.field :label="__('Confirm new password')" for="password_confirmation" required><x-ui.input name="password_confirmation" type="password" autocomplete="new-password" required /></x-ui.field>
                    <x-ui.button>{{ __('Update password') }}</x-ui.button>
                </form>
                <div class="mt-6 space-y-2 border-t border-border pt-5 text-sm text-muted">
                    <p><span class="text-text">{{ __('Role:') }}</span> <x-ui.pill>{{ \App\Support\Labels::role($user->role) }}</x-ui.pill></p>
                    <p class="flex flex-wrap items-center gap-2"><span class="text-text">{{ __('Language:') }}</span> <x-site.language-switch /></p>
                    <p>{{ __('Two-factor authentication:') }} <strong class="text-text">{{ $user->hasEnabledTwoFactorAuthentication() ? __('on') : __('off') }}</strong> · <a href="{{ route('account.security') }}" class="text-accent underline">{{ __('Manage') }}</a></p>
                    <p>{!! __('To request a copy or deletion of your data, see the :link.', ['link' => '<a href="'.e(lroute('legal', 'privacy')).'" class="text-text underline">'.e(__('privacy policy')).'</a>']) !!}</p>
                </div>
            </x-ui.card>
        </div>
    </x-ui.container>
</x-layouts.site>
