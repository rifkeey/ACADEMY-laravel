<x-layouts.admin :title="__('Labs')" :description="__('Practical labs for each course version. Grading runs on the server against the lab\'s answer key.')">
    <form method="GET" class="mb-6 flex flex-wrap items-end gap-3">
        <div class="w-64"><x-ui.field :label="__('Programme')" for="programme">
            <x-ui.select name="programme"><option value="">{{ __('All programmes') }}</option>@foreach ($programmes as $p)<option value="{{ $p->slug }}" @selected($programme?->id === $p->id)>{{ $p->title }}</option>@endforeach</x-ui.select>
        </x-ui.field></div>
        <x-ui.button variant="secondary">{{ __('Filter') }}</x-ui.button>
    </form>

    <x-ui.table :head="[__('Lab'), __('Programme'), __('Type'), __('Pass mark'), __('Status'), __('Learners passed'), '']" :caption="__('Labs')">
        @foreach ($labs as $lab)
            <tr>
                <x-ui.td><p class="font-medium">{{ $lab->title }}</p><p class="text-xs text-subtle">{{ $lab->module?->title ?? __('No module') }}{{ $lab->required ? '' : ' · '.__('optional') }}</p></x-ui.td>
                <x-ui.td>{{ $lab->courseVersion->specialization->title }} <span class="text-xs text-subtle">v{{ $lab->courseVersion->version }} ({{ __(ucfirst($lab->courseVersion->status)) }})</span></x-ui.td>
                <x-ui.td class="text-muted">{{ $lab->content['workspace']['kind'] }} · {{ trans_choice(':count task|:count tasks', count($lab->content['tasks'])) }}</x-ui.td>
                <x-ui.td class="tabular-nums">{{ $lab->pass_percent }}%</x-ui.td>
                <x-ui.td>@include('admin.partials.status', ['value' => $lab->status])</x-ui.td>
                <x-ui.td class="tabular-nums">{{ $completions[$lab->id] ?? 0 }}</x-ui.td>
                <x-ui.td><a href="{{ route('admin.labs.edit', $lab) }}" class="text-accent-strong hover:underline">{{ __('Edit') }}</a></x-ui.td>
            </tr>
        @endforeach
    </x-ui.table>
    <x-admin.pager :items="$labs" />
</x-layouts.admin>
