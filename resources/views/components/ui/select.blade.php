{{-- Select. Pass <option>s in the slot; use `selected` on the chosen one. --}}
@props(['name', 'id' => null])
@php $id ??= $name; @endphp
<select name="{{ $name }}" id="{{ $id }}" {{ $attributes->merge(['class' => 'h-10 w-full rounded-lg border border-border-strong bg-bg px-3 py-2 text-sm text-text focus:border-accent focus:outline-none focus:ring-2 focus:ring-accent/30']) }}>{{ $slot }}</select>
