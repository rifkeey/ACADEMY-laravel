<x-layouts.admin title="Articles" description="Free resource articles shown on the public Resources page.">
    <div class="mb-4 flex justify-end"><x-ui.button :href="route('admin.articles.create')"><x-icon name="plus" class="size-4" /> New article</x-ui.button></div>
    <x-ui.table :head="['Article', 'Status', 'Published', 'Updated', '']" caption="Articles">
        @forelse ($articles as $a)
            <tr>
                <x-ui.td><p class="font-medium">{{ $a->title }}</p><p class="font-mono text-xs text-subtle">/resources/{{ $a->slug }}</p></x-ui.td>
                <x-ui.td>@include('admin.partials.status', ['value' => $a->status])</x-ui.td>
                <x-ui.td class="whitespace-nowrap">{{ $a->published_at?->format('j M Y') ?? '—' }}</x-ui.td>
                <x-ui.td class="whitespace-nowrap text-muted">{{ $a->updated_at->format('j M Y') }}</x-ui.td>
                <x-ui.td><a href="{{ route('admin.articles.edit', $a) }}" class="text-accent-strong hover:underline">Edit</a></x-ui.td>
            </tr>
        @empty
            <tr><x-ui.td colspan="5" class="text-center text-muted">No articles yet.</x-ui.td></tr>
        @endforelse
    </x-ui.table>
    <x-admin.pager :items="$articles" />
</x-layouts.admin>
