@props(['name', 'id' => null, 'value' => ''])
@php
    $id ??= $name;
    $invalid = $errors->has($name);
@endphp
<textarea name="{{ $name }}" id="{{ $id }}" @if ($invalid) aria-invalid="true" aria-describedby="{{ $id }}-error" @endif
    {{ $attributes->merge(['class' => 'min-h-28 w-full rounded-lg border border-border-strong bg-bg px-3 py-2 text-sm text-text placeholder:text-subtle focus:border-accent focus:outline-none focus:ring-2 focus:ring-accent/30 disabled:opacity-60 aria-[invalid=true]:border-danger']) }}>{{ old($name, $value) }}</textarea>
