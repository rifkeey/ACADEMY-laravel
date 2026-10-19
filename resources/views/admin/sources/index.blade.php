@php
    $typeLabel = ['official_standard' => 'Official standard', 'vendor_documentation' => 'Vendor documentation', 'open_resource' => 'Open resource', 'practice_environment' => 'Practice environment'];
@endphp
<x-layouts.admin title="Sources" description="The curriculum source register. Each lesson links to these references; record when a link was last checked.">
    <div class="mb-6 flex flex-wrap gap-2 text-sm">
        <a href="{{ route('admin.sources.index') }}" class="rounded-lg border px-3 py-1.5 {{ ! $status ? 'border-accent text-text' : 'border-border text-muted hover:bg-elevated' }}">All</a>
        @foreach ($states as $s)<a href="{{ route('admin.sources.index', ['status' => $s]) }}" class="rounded-lg border px-3 py-1.5 {{ $status === $s ? 'border-accent text-text' : 'border-border text-muted hover:bg-elevated' }}">{{ str_replace('_', ' ', ucfirst($s)) }}</a>@endforeach
    </div>

    <div class="space-y-3">
        @foreach ($sources as $s)
            <details class="rounded-2xl border border-border bg-surface">
                <summary class="flex cursor-pointer list-none flex-wrap items-center justify-between gap-3 p-4 [&::-webkit-details-marker]:hidden">
                    <span><span class="font-medium">{{ $s->title }}</span> <span class="text-sm text-muted">· {{ $s->publisher }} · used in {{ $s->lessons_count }} lessons</span></span>
                    <span class="flex items-center gap-2 text-xs text-subtle">{{ $s->date_checked?->format('j M Y') ?? 'never checked' }} @include('admin.partials.status', ['value' => $s->verification_status])</span>
                </summary>
                <div class="border-t border-border p-4">
                    <p class="mb-4 text-sm"><a href="{{ $s->url }}" target="_blank" rel="noopener noreferrer" class="break-all text-accent-strong hover:underline">{{ $s->url }}</a></p>
                    @include('admin.sources.form', ['source' => $s, 'action' => route('admin.sources.update', $s), 'method' => 'PUT'])
                </div>
            </details>
        @endforeach
    </div>
    <x-admin.pager :items="$sources" />

    <section aria-labelledby="new-h" class="mt-10">
        <h2 id="new-h" class="text-lg font-semibold">Add a source</h2>
        <div class="mt-4 rounded-2xl border border-dashed border-border-strong p-5">
            @include('admin.sources.form', ['source' => new \App\Models\SourceReference(['source_type' => 'official_standard', 'verification_status' => 'pending_review']), 'action' => route('admin.sources.store'), 'method' => 'POST'])
        </div>
    </section>
</x-layouts.admin>
