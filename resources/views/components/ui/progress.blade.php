@props(['value' => 0, 'label'])
@php $v = max(0, min(100, (int) round($value))); @endphp
<div>
    <div class="mb-1 flex justify-between text-xs text-muted"><span>{{ $label }}</span><span class="tabular-nums">{{ $v }}%</span></div>
    <div role="progressbar" aria-label="{{ $label }}" aria-valuenow="{{ $v }}" aria-valuemin="0" aria-valuemax="100" class="h-2 overflow-hidden rounded-full bg-elevated-2">
        <div class="h-full rounded-full bg-accent" style="width: {{ $v }}%"></div>
    </div>
</div>
