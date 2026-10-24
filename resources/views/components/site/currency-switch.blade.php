{{--
    NGN | FCFA switch: picks which configured price is shown and charged. Plain links, so it works
    without JavaScript. <x-site.currency-switch />
--}}
@php $current = \App\Support\Currency::current(); @endphp
<nav {{ $attributes->merge(['class' => 'inline-flex items-center gap-2 text-xs']) }} aria-label="{{ __('Currency') }}">
    <span class="text-subtle">{{ __('Prices in:') }}</span>
    <span class="flex items-center rounded-lg border border-border font-semibold">
        @foreach (\App\Support\Currency::supported() as $code)
            @if (! $loop->first)<span class="text-subtle" aria-hidden="true">|</span>@endif
            <a href="{{ route('currency.switch', ['currency' => $code, 'to' => request()->getRequestUri()]) }}"
               @class(['rounded-md px-2.5 py-1 focus-visible:outline-2 focus-visible:outline-accent', 'text-text' => $code === $current, 'text-muted hover:text-text' => $code !== $current])
               @if ($code === $current) aria-current="true" @endif>{{ \App\Support\Currency::short($code) }}</a>
        @endforeach
    </span>
</nav>
