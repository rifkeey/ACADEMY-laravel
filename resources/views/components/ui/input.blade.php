{{-- Text input that marks itself invalid (and links its error) when validation fails. --}}
@props(['name', 'id' => null, 'value' => null, 'type' => 'text'])
@php
    $id ??= $name;
    $invalid = $errors->has($name);
    // Never re-fill password fields from old input.
    $current = $type === 'password' ? null : old($name, $value);
@endphp
<input type="{{ $type }}" name="{{ $name }}" id="{{ $id }}" @if ($current !== null) value="{{ $current }}" @endif
    @if ($invalid) aria-invalid="true" aria-describedby="{{ $id }}-error" @endif
    {{ $attributes->merge(['class' => 'h-10 w-full rounded-lg border border-border-strong bg-bg px-3 py-2 text-sm text-text placeholder:text-subtle focus:border-accent focus:outline-none focus:ring-2 focus:ring-accent/30 disabled:opacity-60 aria-[invalid=true]:border-danger']) }}>
