{{-- Label + control + hint/error. Pass the control in the slot with matching id. --}}
@props(['label', 'for', 'hint' => null, 'required' => false])
<div class="space-y-1.5">
    <label for="{{ $for }}" class="block text-sm font-medium text-text">{{ $label }}@if ($required)<span class="ml-0.5 text-danger" aria-hidden="true">*</span>@endif</label>
    {{ $slot }}
    @error($attributes->get('name', $for))
        <p id="{{ $for }}-error" class="text-xs text-danger">{{ $message }}</p>
    @else
        @if ($hint)<p class="text-xs text-subtle">{{ $hint }}</p>@endif
    @enderror
</div>
