{{--
    Destructive action behind an accessible confirmation dialog (native <dialog>).
    <x-ui.confirm :action="route(...)" label="Revoke" title="..." description="..." require-reason />
--}}
@props(['action', 'method' => 'POST', 'label', 'title', 'description', 'confirmLabel' => null, 'requireReason' => false, 'variant' => 'danger'])
@php $id = 'dlg-'.\Illuminate\Support\Str::random(8); @endphp
<div x-data="confirmDialog">
    <x-ui.button type="button" :variant="$variant" size="sm" @click="open">{{ $label }}</x-ui.button>
    <dialog x-ref="dialog" aria-labelledby="{{ $id }}-title" class="m-auto w-[min(92vw,28rem)] rounded-2xl border border-border-strong bg-surface p-0 text-text backdrop:bg-black/70">
        <form method="POST" action="{{ $action }}" class="space-y-4 p-6">
            @csrf
            @if (strtoupper($method) !== 'POST') @method($method) @endif
            {{ $slot }}
            <h2 id="{{ $id }}-title" class="text-lg font-semibold">{{ $title }}</h2>
            <p class="text-sm text-muted">{{ $description }}</p>
            @if ($requireReason)
                <div class="space-y-1.5">
                    <label for="{{ $id }}-reason" class="text-sm font-medium">{{ __('Reason (recorded in the audit log)') }}</label>
                    <textarea id="{{ $id }}-reason" name="reason" required minlength="5" maxlength="500" class="w-full rounded-lg border border-border-strong bg-bg p-2 text-sm"></textarea>
                </div>
            @endif
            <div class="flex justify-end gap-2">
                <x-ui.button type="button" variant="ghost" size="sm" @click="close">{{ __('Cancel') }}</x-ui.button>
                <x-ui.button :variant="$variant" size="sm">{{ $confirmLabel ?? __('Confirm') }}</x-ui.button>
            </div>
        </form>
    </dialog>
</div>
