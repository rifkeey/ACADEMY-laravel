<x-layouts.admin title="Enquiries" description="Messages from the contact and corporate forms. Reply from your own email client; record progress here.">
    <div class="mb-6 flex flex-wrap gap-2 text-sm">
        <a href="{{ route('admin.enquiries.index') }}" class="rounded-lg border px-3 py-1.5 {{ ! $status ? 'border-accent text-text' : 'border-border text-muted hover:bg-elevated' }}">All</a>
        @foreach (['new' => 'New', 'in_progress' => 'In progress', 'closed' => 'Closed'] as $k => $label)
            <a href="{{ route('admin.enquiries.index', ['status' => $k]) }}" class="rounded-lg border px-3 py-1.5 {{ $status === $k ? 'border-accent text-text' : 'border-border text-muted hover:bg-elevated' }}">{{ $label }} ({{ $counts[$k] ?? 0 }})</a>
        @endforeach
    </div>

    <div class="space-y-4">
        @forelse ($enquiries as $e)
            <x-ui.card class="p-5">
                <div class="flex flex-wrap items-start justify-between gap-3">
                    <div>
                        <p class="font-semibold">{{ $e->name }} <span class="font-normal text-muted">· <a href="mailto:{{ $e->email }}" class="hover:underline">{{ $e->email }}</a></span></p>
                        <p class="text-xs text-subtle">{{ str_replace('_', ' ', ucfirst($e->kind)) }} · {{ $e->created_at->format('j M Y H:i') }}@if ($e->organisation) · {{ $e->organisation }}@endif @if ($e->team_size) · team {{ $e->team_size }}@endif @if ($e->specialization_slug) · {{ $e->specialization_slug }}@endif @if ($e->phone) · {{ $e->phone }}@endif</p>
                    </div>
                    @include('admin.partials.status', ['value' => $e->status])
                </div>
                <p class="mt-3 whitespace-pre-line text-sm">{{ $e->message }}</p>
                <form method="POST" action="{{ route('admin.enquiries.update', $e) }}" class="mt-4 grid gap-3 border-t border-border pt-4 sm:grid-cols-[10rem_1fr_auto] sm:items-end">
                    @csrf @method('PUT')
                    <x-ui.field label="Status" for="s{{ $e->id }}"><x-ui.select name="status" id="s{{ $e->id }}">@foreach (['new' => 'New', 'in_progress' => 'In progress', 'closed' => 'Closed'] as $k => $label)<option value="{{ $k }}" @selected($e->status === $k)>{{ $label }}</option>@endforeach</x-ui.select></x-ui.field>
                    <x-ui.field label="Internal notes" for="n{{ $e->id }}"><x-ui.input name="notes" id="n{{ $e->id }}" :value="$e->notes" maxlength="4000" /></x-ui.field>
                    <x-ui.button size="sm" variant="secondary">Save</x-ui.button>
                </form>
            </x-ui.card>
        @empty
            <x-ui.empty title="No enquiries" icon="inbox" />
        @endforelse
    </div>
    <x-admin.pager :items="$enquiries" />
</x-layouts.admin>
