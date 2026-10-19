<x-layouts.admin title="Labs" description="Practical labs for each course version. Grading runs on the server against the lab's answer key.">
    <form method="GET" class="mb-6 flex flex-wrap items-end gap-3">
        <div class="w-64"><x-ui.field label="Programme" for="programme">
            <x-ui.select name="programme"><option value="">All programmes</option>@foreach ($programmes as $p)<option value="{{ $p->slug }}" @selected($programme?->id === $p->id)>{{ $p->title }}</option>@endforeach</x-ui.select>
        </x-ui.field></div>
        <x-ui.button variant="secondary">Filter</x-ui.button>
    </form>

    <x-ui.table :head="['Lab', 'Programme', 'Type', 'Pass mark', 'Status', 'Learners passed', '']" caption="Labs">
        @foreach ($labs as $lab)
            <tr>
                <x-ui.td><p class="font-medium">{{ $lab->title }}</p><p class="text-xs text-subtle">{{ $lab->module?->title ?? 'No module' }}{{ $lab->required ? '' : ' · optional' }}</p></x-ui.td>
                <x-ui.td>{{ $lab->courseVersion->specialization->title }} <span class="text-xs text-subtle">v{{ $lab->courseVersion->version }} ({{ $lab->courseVersion->status }})</span></x-ui.td>
                <x-ui.td class="text-muted">{{ $lab->content['workspace']['kind'] }} · {{ count($lab->content['tasks']) }} tasks</x-ui.td>
                <x-ui.td class="tabular-nums">{{ $lab->pass_percent }}%</x-ui.td>
                <x-ui.td>@include('admin.partials.status', ['value' => $lab->status])</x-ui.td>
                <x-ui.td class="tabular-nums">{{ $completions[$lab->id] ?? 0 }}</x-ui.td>
                <x-ui.td><a href="{{ route('admin.labs.edit', $lab) }}" class="text-accent-strong hover:underline">Edit</a></x-ui.td>
            </tr>
        @endforeach
    </x-ui.table>
    <x-admin.pager :items="$labs" />
</x-layouts.admin>
