{{-- Public and learner pages: header, main content, footer, and one-time flash messages. --}}
@props(['title' => null, 'description' => null, 'noindex' => false])
<x-layouts.base :title="$title" :description="$description" :noindex="$noindex">
    @isset($head)
        <x-slot:head>{{ $head }}</x-slot:head>
    @endisset
    <x-site.header />
    <main id="main">
        @foreach (['success' => 'success', 'warning' => 'warning', 'error' => 'danger'] as $key => $tone)
            @if (session($key))
                <x-ui.container class="pt-6"><x-ui.alert :tone="$tone">{{ session($key) }}</x-ui.alert></x-ui.container>
            @endif
        @endforeach
        {{ $slot }}
    </main>
    <x-site.footer />
</x-layouts.base>
