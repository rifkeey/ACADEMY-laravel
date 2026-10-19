@php
    $live = $version->status !== 'draft';
    $selectedSources = old('sources', $lesson->sources->pluck('id')->all());
@endphp
<x-layouts.admin :title="$lesson->title" :description="$version->specialization->title.' · version '.$version->version.' ('.$version->status.') · module '.$lesson->module->position.': '.$lesson->module->title">
    <p class="mb-6 text-sm"><a href="{{ route('admin.programmes.edit', [$version->specialization, 'version' => $version->id]) }}" class="text-accent-strong hover:underline">← Back to course structure</a></p>
    @if ($version->status === 'published')
        <x-ui.alert tone="info" class="mb-6">This lesson is in the live version. You can correct its text; changing whether it is required or visible needs a draft version.</x-ui.alert>
    @endif

    <form method="POST" action="{{ route('admin.lessons.update', $lesson) }}" class="grid gap-6 xl:grid-cols-[1fr_20rem]">
        @csrf @method('PUT')
        <div class="space-y-5">
            <x-ui.field label="Title" for="title"><x-ui.input name="title" :value="$lesson->title" required /></x-ui.field>
            <x-ui.field label="Objectives (one per line)" for="objectives"><x-ui.textarea name="objectives" :value="\App\Support\Lines::join($lesson->objectives)" rows="4" /></x-ui.field>
            <x-ui.field label="Lesson body (Markdown)" for="body_md" hint="Raw HTML is escaped when the lesson is shown. Use Markdown headings, lists, tables and code blocks."><x-ui.textarea name="body_md" :value="$lesson->body_md" rows="28" class="font-mono text-xs" required /></x-ui.field>
            <x-ui.field label="Knowledge checks (JSON)" for="knowledge_checks" hint="A JSON list of objects with question, options, answerIndex (0-based) and explanation. These are ungraded self-checks.">
                <x-ui.textarea name="knowledge_checks" :value="json_encode($lesson->knowledge_checks ?? [], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)" rows="14" class="font-mono text-xs" />
            </x-ui.field>
        </div>
        <div class="space-y-5">
            <x-ui.card class="space-y-4 p-5">
                <x-ui.field label="Estimated minutes" for="estimated_minutes"><x-ui.input type="number" name="estimated_minutes" :value="$lesson->estimated_minutes" min="1" required /></x-ui.field>
                <x-ui.field label="Required for completion" for="required"><x-ui.select name="required"><option value="1" @selected($lesson->required)>Required</option><option value="0" @selected(! $lesson->required)>Optional</option></x-ui.select></x-ui.field>
                <x-ui.field label="Status" for="status"><x-ui.select name="status">@foreach (['draft', 'published', 'archived'] as $s)<option value="{{ $s }}" @selected($lesson->status === $s)>{{ ucfirst($s) }}</option>@endforeach</x-ui.select></x-ui.field>
                <x-ui.button class="w-full">Save lesson</x-ui.button>
            </x-ui.card>
            <x-ui.card class="p-5">
                <fieldset>
                    <legend class="text-sm font-semibold">References</legend>
                    <div class="mt-3 max-h-96 space-y-1.5 overflow-y-auto pr-1">
                        @foreach ($sources as $s)
                            <label class="flex items-start gap-2 text-sm"><input type="checkbox" name="sources[]" value="{{ $s->id }}" @checked(in_array($s->id, $selectedSources)) class="mt-1 accent-[var(--accent)]"><span>{{ $s->title }} <span class="text-xs text-subtle">· {{ $s->publisher }}</span></span></label>
                        @endforeach
                    </div>
                </fieldset>
            </x-ui.card>
        </div>
    </form>
</x-layouts.admin>
