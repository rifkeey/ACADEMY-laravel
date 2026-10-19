<x-layouts.admin title="Audit log" description="Every sensitive action, who made it and when. Entries cannot be edited or deleted from the application.">
    <form method="GET" class="mb-6 flex flex-wrap items-end gap-3">
        <div class="w-56"><x-ui.field label="Action starts with" for="action"><x-ui.input name="action" :value="$action" placeholder="e.g. certificate." class="font-mono" /></x-ui.field></div>
        <div class="w-56"><x-ui.field label="Entity" for="entity"><x-ui.select name="entity"><option value="">Any</option>@foreach ($entities as $e)<option value="{{ $e }}" @selected($entity === $e)>{{ $e }}</option>@endforeach</x-ui.select></x-ui.field></div>
        <x-ui.button variant="secondary">Filter</x-ui.button>
    </form>
    <x-ui.table :head="['When (UTC)', 'Who', 'Action', 'Entity', 'Details']" caption="Audit events">
        @forelse ($events as $e)
            <tr>
                <x-ui.td class="whitespace-nowrap text-muted">{{ $e->created_at->format('j M Y H:i:s') }}</x-ui.td>
                <x-ui.td>{{ $e->actor?->name ?? 'System' }}@if ($e->actor)<p class="text-xs text-subtle">{{ $e->actor->email }}</p>@endif</x-ui.td>
                <x-ui.td class="font-mono text-xs text-accent">{{ $e->action }}</x-ui.td>
                <x-ui.td class="font-mono text-xs">{{ $e->entity_type }} {{ $e->entity_id }}</x-ui.td>
                <x-ui.td class="max-w-md"><code class="block break-all text-xs text-muted">{{ json_encode($e->details, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) }}</code></x-ui.td>
            </tr>
        @empty
            <tr><x-ui.td colspan="5" class="text-center text-muted">No events.</x-ui.td></tr>
        @endforelse
    </x-ui.table>
    <x-admin.pager :items="$events" />
</x-layouts.admin>
