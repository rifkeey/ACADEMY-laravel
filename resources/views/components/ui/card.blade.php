@props(['as' => 'div'])
<{{ $as }} {{ $attributes->merge(['class' => 'rounded-2xl border border-border bg-surface']) }}>{{ $slot }}</{{ $as }}>
