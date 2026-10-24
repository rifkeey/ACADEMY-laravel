@php
    // The bar shows the main links; the rest sit under "More" so the header fits on one line in
    // every language (French labels are much longer). The mobile menu lists everything.
    $main = [
        ['programmes.index', __('Programmes')],
        ['corporate', __('Corporate training')],
        ['resources.index', __('Resources')],
    ];
    $more = [
        ['verify.index', __('Verify a certificate')],
        ['about', __('About')],
    ];
    $nav = [...$main, ...$more];
    $user = auth()->user();
    $isCurrent = fn (string $route) => request()->routeIs($route, \App\Support\Locales::routeName($route));
@endphp
<header class="sticky top-0 z-40 border-b border-border bg-bg/90 backdrop-blur supports-[backdrop-filter]:bg-bg/75">
    <x-ui.container class="flex h-16 items-center justify-between gap-4">
        <a href="{{ lroute('home') }}" class="shrink-0 text-text" aria-label="{{ __('AMANIQ Academy home') }}">
            <x-brand.lockup division="Academy" size="sm" />
        </a>

        <nav aria-label="{{ __('Main') }}" class="hidden items-center gap-1 xl:flex">
            @foreach ($main as [$route, $label])
                <a href="{{ lroute($route) }}" @class(['whitespace-nowrap rounded-md px-3 py-2 text-sm hover:bg-elevated hover:text-text', 'text-text' => $isCurrent($route), 'text-muted' => ! $isCurrent($route)]) @if ($isCurrent($route)) aria-current="page" @endif>{{ $label }}</a>
            @endforeach
            @php $moreCurrent = collect($more)->contains(fn ($m) => $isCurrent($m[0])); @endphp
            <details class="group relative" data-dropdown>
                <summary @class(['flex cursor-pointer list-none items-center gap-1 whitespace-nowrap rounded-md px-3 py-2 text-sm hover:bg-elevated hover:text-text [&::-webkit-details-marker]:hidden', 'text-text' => $moreCurrent, 'text-muted' => ! $moreCurrent])>
                    {{ __('More') }} <x-icon name="chevron-down" class="size-3.5 transition-transform group-open:rotate-180" />
                </summary>
                <div class="absolute left-0 mt-2 w-56 rounded-xl border border-border-strong bg-surface p-2 shadow-2xl">
                    @foreach ($more as [$route, $label])
                        <a href="{{ lroute($route) }}" @class(['block rounded-md px-3 py-2 text-sm hover:bg-elevated', 'text-text font-medium' => $isCurrent($route), 'text-muted hover:text-text' => ! $isCurrent($route)]) @if ($isCurrent($route)) aria-current="page" @endif>{{ $label }}</a>
                    @endforeach
                </div>
            </details>
        </nav>

        {{-- Never shrinks: the buttons keep their size and labels stay on one line in every language. --}}
        <div class="flex shrink-0 items-center gap-2">
            <x-site.language-switch />

            <div class="hidden items-center gap-2 xl:flex">
                @auth
                    <x-ui.button :href="route('dashboard')" variant="secondary" size="sm" class="whitespace-nowrap">{{ __('Dashboard') }}</x-ui.button>
                    {{-- Account menu keeps the bar short: Admin, account settings and sign-out live here. --}}
                    <details class="group relative" data-dropdown>
                        <summary class="flex h-8 cursor-pointer list-none items-center gap-1 whitespace-nowrap rounded-lg px-3 text-sm font-semibold text-muted hover:bg-elevated hover:text-text [&::-webkit-details-marker]:hidden">
                            {{ __('Account') }} <x-icon name="chevron-down" class="size-3.5 transition-transform group-open:rotate-180" />
                        </summary>
                        <div class="absolute right-0 mt-2 w-56 rounded-xl border border-border-strong bg-surface p-2 shadow-2xl">
                            <p class="truncate px-3 pb-2 pt-1 text-xs text-subtle">{{ $user->email }}</p>
                            @if ($user->isStaff())
                                <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-2 rounded-md px-3 py-2 text-sm text-muted hover:bg-elevated hover:text-text"><x-icon name="shield-check" class="size-4" /> {{ __('Admin') }}</a>
                            @endif
                            <a href="{{ route('account') }}" class="block rounded-md px-3 py-2 text-sm text-muted hover:bg-elevated hover:text-text">{{ __('My account') }}</a>
                            <form method="POST" action="{{ route('logout') }}">@csrf<button class="w-full rounded-md px-3 py-2 text-left text-sm text-muted hover:bg-elevated hover:text-text">{{ __('Sign out') }}</button></form>
                        </div>
                    </details>
                @else
                    <x-ui.button :href="route('login')" variant="ghost" size="sm" class="whitespace-nowrap">{{ __('Sign in') }}</x-ui.button>
                    <x-ui.button :href="route('register')" size="sm" class="whitespace-nowrap">{{ __('Create account') }}</x-ui.button>
                @endauth
            </div>

            {{-- Mobile menu: a native <details> element, so it works without JavaScript. --}}
            <details class="group relative xl:hidden" data-dropdown>
                <summary class="flex size-10 cursor-pointer list-none items-center justify-center rounded-lg border border-border text-muted [&::-webkit-details-marker]:hidden" aria-label="{{ __('Open menu') }}">
                    <x-icon name="menu" class="size-5" />
                </summary>
                <div class="absolute right-0 mt-2 w-64 rounded-xl border border-border-strong bg-surface p-2 shadow-2xl">
                    <nav aria-label="{{ __('Mobile') }}" class="flex flex-col">
                        @foreach ($nav as [$route, $label])
                            <a href="{{ lroute($route) }}" class="rounded-md px-3 py-2.5 text-sm text-text hover:bg-elevated" @if ($isCurrent($route)) aria-current="page" @endif>{{ $label }}</a>
                        @endforeach
                        <div class="my-2 border-t border-border"></div>
                        @auth
                            <a href="{{ route('dashboard') }}" class="rounded-md px-3 py-2.5 text-sm hover:bg-elevated">{{ __('Dashboard') }}</a>
                            @if ($user->isStaff())<a href="{{ route('admin.dashboard') }}" class="rounded-md px-3 py-2.5 text-sm hover:bg-elevated">{{ __('Admin') }}</a>@endif
                            <a href="{{ route('account') }}" class="rounded-md px-3 py-2.5 text-sm hover:bg-elevated">{{ __('My account') }}</a>
                            <form method="POST" action="{{ route('logout') }}">@csrf<button class="w-full rounded-md px-3 py-2.5 text-left text-sm text-muted hover:bg-elevated">{{ __('Sign out') }}</button></form>
                        @else
                            <a href="{{ route('login') }}" class="rounded-md px-3 py-2.5 text-sm hover:bg-elevated">{{ __('Sign in') }}</a>
                            <a href="{{ route('register') }}" class="rounded-md px-3 py-2.5 text-sm font-semibold text-accent hover:bg-elevated">{{ __('Create account') }}</a>
                        @endauth
                    </nav>
                </div>
            </details>
        </div>
    </x-ui.container>
</header>
