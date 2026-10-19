@php
    $nav = [
        ['programmes.index', 'Programmes'],
        ['corporate', 'Corporate training'],
        ['resources.index', 'Resources'],
        ['verify.index', 'Verify a certificate'],
        ['about', 'About'],
    ];
    $user = auth()->user();
@endphp
<header class="sticky top-0 z-40 border-b border-border bg-bg/90 backdrop-blur supports-[backdrop-filter]:bg-bg/75">
    <x-ui.container class="flex h-16 items-center justify-between gap-4">
        <a href="{{ route('home') }}" class="shrink-0 text-text" aria-label="AMANIQ Academy home">
            <x-brand.lockup division="Academy" size="sm" />
        </a>

        <nav aria-label="Main" class="hidden items-center gap-1 xl:flex">
            @foreach ($nav as [$route, $label])
                <a href="{{ route($route) }}" @class(['whitespace-nowrap rounded-md px-3 py-2 text-sm hover:bg-elevated hover:text-text', 'text-text' => request()->routeIs($route), 'text-muted' => ! request()->routeIs($route)]) @if (request()->routeIs($route)) aria-current="page" @endif>{{ $label }}</a>
            @endforeach
        </nav>

        <div class="hidden items-center gap-2 xl:flex">
            @auth
                @if ($user->isStaff())
                    <x-ui.button :href="route('admin.dashboard')" variant="ghost" size="sm"><x-icon name="shield-check" /> Admin</x-ui.button>
                @endif
                <x-ui.button :href="route('dashboard')" variant="secondary" size="sm">Dashboard</x-ui.button>
                <form method="POST" action="{{ route('logout') }}">@csrf<button class="rounded-md px-3 py-2 text-sm text-muted hover:text-text">Sign out</button></form>
            @else
                <x-ui.button :href="route('login')" variant="ghost" size="sm">Sign in</x-ui.button>
                <x-ui.button :href="route('register')" size="sm">Create account</x-ui.button>
            @endauth
        </div>

        {{-- Mobile menu: a native <details> element, so it works without JavaScript. --}}
        <details class="group relative xl:hidden">
            <summary class="flex size-10 cursor-pointer list-none items-center justify-center rounded-lg border border-border text-muted [&::-webkit-details-marker]:hidden" aria-label="Open menu">
                <x-icon name="menu" class="size-5" />
            </summary>
            <div class="absolute right-0 mt-2 w-64 rounded-xl border border-border-strong bg-surface p-2 shadow-2xl">
                <nav aria-label="Mobile" class="flex flex-col">
                    @foreach ($nav as [$route, $label])
                        <a href="{{ route($route) }}" class="rounded-md px-3 py-2.5 text-sm text-text hover:bg-elevated">{{ $label }}</a>
                    @endforeach
                    <div class="my-2 border-t border-border"></div>
                    @auth
                        <a href="{{ route('dashboard') }}" class="rounded-md px-3 py-2.5 text-sm hover:bg-elevated">Dashboard</a>
                        @if ($user->isStaff())<a href="{{ route('admin.dashboard') }}" class="rounded-md px-3 py-2.5 text-sm hover:bg-elevated">Admin</a>@endif
                        <form method="POST" action="{{ route('logout') }}">@csrf<button class="w-full rounded-md px-3 py-2.5 text-left text-sm text-muted hover:bg-elevated">Sign out</button></form>
                    @else
                        <a href="{{ route('login') }}" class="rounded-md px-3 py-2.5 text-sm hover:bg-elevated">Sign in</a>
                        <a href="{{ route('register') }}" class="rounded-md px-3 py-2.5 text-sm font-semibold text-accent hover:bg-elevated">Create account</a>
                    @endauth
                </nav>
            </div>
        </details>
    </x-ui.container>
</header>
