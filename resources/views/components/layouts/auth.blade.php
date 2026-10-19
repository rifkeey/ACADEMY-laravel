{{-- Centred card for sign-in and account flows. Never indexed. --}}
@props(['title'])
<x-layouts.site :title="$title" :noindex="true">
    <x-ui.container class="flex justify-center py-16 sm:py-24">
        <div class="w-full max-w-md rounded-2xl border border-border bg-surface p-6 sm:p-8">
            <h1 class="text-2xl font-semibold">{{ $title }}</h1>
            @isset($intro)<p class="mt-1 mb-6 text-sm text-muted">{{ $intro }}</p>@else<div class="mb-6"></div>@endisset
            @if ($errors->any() && ! $errors->hasAny(['name', 'email', 'password', 'password_confirmation', 'terms', 'code', 'recovery_code']))
                <x-ui.alert tone="danger" class="mb-5">{{ $errors->first() }}</x-ui.alert>
            @endif
            {{ $slot }}
        </div>
    </x-ui.container>
</x-layouts.site>
