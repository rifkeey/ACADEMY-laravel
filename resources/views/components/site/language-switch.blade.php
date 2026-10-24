{{--
    EN | FR language switch. Each option is a plain link (works without JavaScript) to the
    switcher, which saves the choice and returns to the same page in that language.
--}}
@php $current = app()->getLocale(); @endphp
<nav {{ $attributes->merge(['class' => 'flex items-center rounded-lg border border-border text-xs font-semibold']) }} aria-label="{{ __('Language') }}">
    @foreach (config('academy.locales') as $code => $meta)
        @if (! $loop->first)<span class="text-subtle" aria-hidden="true">|</span>@endif
        <a href="{{ route('locale.switch', ['locale' => $code, 'to' => request()->getRequestUri()]) }}"
           lang="{{ $meta['html'] }}" hreflang="{{ $code }}"
           @class(['rounded-md px-2.5 py-1.5 focus-visible:outline-2 focus-visible:outline-accent', 'text-text' => $code === $current, 'text-muted hover:text-text' => $code !== $current])
           @if ($code === $current) aria-current="true" @endif>
            <span aria-hidden="true">{{ $meta['short'] }}</span><span class="sr-only">{{ $meta['name'] }}</span>
        </a>
    @endforeach
</nav>
