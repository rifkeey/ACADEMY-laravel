@php
    $version = $lab->courseVersion;
    $json = fn ($v) => json_encode($v, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
@endphp
<x-layouts.admin :title="'Lab: '.$lab->title" :description="$version->specialization->title.' · version '.$version->version.' ('.$version->status.')'">
    <p class="mb-6 text-sm"><a href="{{ route('admin.programmes.edit', [$version->specialization, 'version' => $version->id]) }}" class="text-accent-strong hover:underline">← Back to course structure</a></p>
    @if ($version->status === 'published')
        <x-ui.alert tone="info" class="mb-6">This lab is in the live version. Title, summary, difficulty, timing and scenario wording can be corrected. Tasks, workspace data, the answer key, pass mark, required and status need a draft version, so learners are never graded against rules that changed under them.</x-ui.alert>
    @endif

    <form method="POST" action="{{ route('admin.labs.update', $lab) }}" class="grid gap-6 xl:grid-cols-[1fr_20rem]">
        @csrf @method('PUT')
        <div class="space-y-5">
            <x-ui.field label="Title" for="title"><x-ui.input name="title" :value="$lab->title" required /></x-ui.field>
            <x-ui.field label="Summary" for="summary"><x-ui.textarea name="summary" :value="$lab->summary" rows="2" required /></x-ui.field>
            <x-ui.field label="Learner-visible content (JSON)" for="content" hint="Scenario, objectives, instructions, workspace data and tasks. Never put answers here.">
                <x-ui.textarea name="content" :value="$json($lab->content)" rows="24" class="font-mono text-xs" required spellcheck="false" />
            </x-ui.field>
            <x-ui.field label="Answer key (JSON, server only)" for="validator" hint="One entry per task id with the same type and correct/incorrect feedback. Never sent to learners.">
                <x-ui.textarea name="validator" :value="$json($lab->validator)" rows="18" class="font-mono text-xs" required spellcheck="false" />
            </x-ui.field>
        </div>
        <div>
            <x-ui.card class="space-y-4 p-5 xl:sticky xl:top-6">
                <x-ui.field label="Difficulty" for="difficulty"><x-ui.select name="difficulty">@foreach (['foundation', 'intermediate', 'advanced'] as $d)<option value="{{ $d }}" @selected($lab->difficulty === $d)>{{ ucfirst($d) }}</option>@endforeach</x-ui.select></x-ui.field>
                <x-ui.field label="Estimated minutes" for="estimated_minutes"><x-ui.input type="number" name="estimated_minutes" :value="$lab->estimated_minutes" min="5" required /></x-ui.field>
                <x-ui.field label="Pass mark (%)" for="pass_percent"><x-ui.input type="number" name="pass_percent" :value="$lab->pass_percent" min="50" max="100" required /></x-ui.field>
                <x-ui.field label="Required for completion" for="required"><x-ui.select name="required"><option value="1" @selected($lab->required)>Required</option><option value="0" @selected(! $lab->required)>Optional</option></x-ui.select></x-ui.field>
                <x-ui.field label="Status" for="status"><x-ui.select name="status">@foreach (['draft', 'published', 'archived'] as $s)<option value="{{ $s }}" @selected($lab->status === $s)>{{ ucfirst($s) }}</option>@endforeach</x-ui.select></x-ui.field>
                <p class="text-xs text-muted">On save the definition is checked and run once through the grader; invalid JSON or mismatched task ids are rejected.</p>
                <x-ui.button class="w-full">Save lab</x-ui.button>
            </x-ui.card>
        </div>
    </form>
</x-layouts.admin>
