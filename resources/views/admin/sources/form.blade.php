{{-- Source reference form, used for editing an existing source and adding a new one. --}}
@php
    $p = $source->exists ? 's'.$source->id.'-' : 'new-';
    $types = ['official_standard' => __('Official standard'), 'vendor_documentation' => __('Vendor documentation'), 'open_resource' => __('Open resource'), 'practice_environment' => __('Practice environment')];
@endphp
<form method="POST" action="{{ $action }}" class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
    @csrf
    @if ($method !== 'POST') @method($method) @endif
    <x-ui.field :label="__('Key')" for="{{ $p }}key"><input id="{{ $p }}key" name="key" value="{{ $source->key }}" required pattern="[a-z0-9-]+" maxlength="60" class="h-10 w-full rounded-lg border border-border-strong bg-bg px-3 font-mono text-sm"></x-ui.field>
    <div class="lg:col-span-2"><x-ui.field :label="__('Title')" for="{{ $p }}title"><input id="{{ $p }}title" name="title" value="{{ $source->title }}" required maxlength="300" class="h-10 w-full rounded-lg border border-border-strong bg-bg px-3 text-sm"></x-ui.field></div>
    <x-ui.field :label="__('Publisher')" for="{{ $p }}publisher"><input id="{{ $p }}publisher" name="publisher" value="{{ $source->publisher }}" required maxlength="160" class="h-10 w-full rounded-lg border border-border-strong bg-bg px-3 text-sm"></x-ui.field>
    <div class="lg:col-span-2"><x-ui.field :label="__('URL (https)')" for="{{ $p }}url"><input id="{{ $p }}url" type="url" name="url" value="{{ $source->url }}" required maxlength="500" class="h-10 w-full rounded-lg border border-border-strong bg-bg px-3 text-sm"></x-ui.field></div>
    <x-ui.field :label="__('Topic')" for="{{ $p }}topic"><input id="{{ $p }}topic" name="topic" value="{{ $source->topic }}" required maxlength="200" class="h-10 w-full rounded-lg border border-border-strong bg-bg px-3 text-sm"></x-ui.field>
    <x-ui.field :label="__('Type')" for="{{ $p }}type"><select id="{{ $p }}type" name="source_type" class="h-10 w-full rounded-lg border border-border-strong bg-bg px-3 text-sm">@foreach ($types as $k => $label)<option value="{{ $k }}" @selected($source->source_type === $k)>{{ $label }}</option>@endforeach</select></x-ui.field>
    <x-ui.field :label="__('Verification')" for="{{ $p }}vs"><select id="{{ $p }}vs" name="verification_status" class="h-10 w-full rounded-lg border border-border-strong bg-bg px-3 text-sm">@foreach (['verified' => 'Verified', 'pending_review' => 'Pending review', 'unavailable' => 'Unavailable'] as $k => $label)<option value="{{ $k }}" @selected($source->verification_status === $k)>{{ $label }}</option>@endforeach</select></x-ui.field>
    <x-ui.field :label="__('Date checked')" for="{{ $p }}dc"><input id="{{ $p }}dc" type="date" name="date_checked" value="{{ $source->date_checked?->format('Y-m-d') }}" class="h-10 w-full rounded-lg border border-border-strong bg-bg px-3 text-sm"></x-ui.field>
    <x-ui.field :label="__('Version note')" for="{{ $p }}vn"><input id="{{ $p }}vn" name="version_note" value="{{ $source->version_note }}" maxlength="2000" class="h-10 w-full rounded-lg border border-border-strong bg-bg px-3 text-sm"></x-ui.field>
    <x-ui.field :label="__('What it supports')" for="{{ $p }}sup"><input id="{{ $p }}sup" name="supports" value="{{ $source->supports }}" maxlength="2000" class="h-10 w-full rounded-lg border border-border-strong bg-bg px-3 text-sm"></x-ui.field>
    <div class="flex items-end"><x-ui.button size="sm" :variant="$source->exists ? 'secondary' : 'primary'">{{ $source->exists ? __('Save source') : __('Add source') }}</x-ui.button></div>
</form>
