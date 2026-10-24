@php
    $version = $lab->courseVersion;
    $json = fn ($v) => json_encode($v, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
@endphp
<x-layouts.admin :title="'Lab: '.$lab->title" :description="$version->specialization->title.' · version '.$version->version.' ('.$version->status.')'">
    <p class="mb-6 text-sm"><a href="{{ route('admin.programmes.edit', [$version->specialization, 'version' => $version->id]) }}" class="text-accent-strong hover:underline">{{ __('← Back to course structure') }}</a></p>
    @if ($version->status === 'published')
        <x-ui.alert tone="info" class="mb-6">{{ __('This lab is in the live version. Title, summary, difficulty, timing and scenario wording can be corrected. Tasks, workspace data, the answer key, pass mark, required and status need a draft version, so learners are never graded against rules that changed under them.') }}</x-ui.alert>
    @endif

    <form method="POST" action="{{ route('admin.labs.update', $lab) }}" class="grid gap-6 xl:grid-cols-[1fr_20rem]">
        @csrf @method('PUT')
        <div class="space-y-5">
            <x-ui.field :label="__('Title')" for="title"><x-ui.input name="title" :value="$lab->title" required /></x-ui.field>
            <x-ui.field :label="__('Summary')" for="summary"><x-ui.textarea name="summary" :value="$lab->summary" rows="2" required /></x-ui.field>
            <x-ui.field :label="__('Learner-visible content (JSON)')" for="content" :hint="__('Scenario, objectives, instructions, workspace data and tasks. Never put answers here.')">
                <x-ui.textarea name="content" :value="$json($lab->content)" rows="24" class="font-mono text-xs" required spellcheck="false" />
            </x-ui.field>
            <x-ui.field :label="__('Answer key (JSON, server only)')" for="validator" :hint="__('One entry per task id with the same type and correct/incorrect feedback. Never sent to learners.')">
                <x-ui.textarea name="validator" :value="$json($lab->validator)" rows="18" class="font-mono text-xs" required spellcheck="false" />
            </x-ui.field>
        </div>
        <div>
            <x-ui.card class="space-y-4 p-5 xl:sticky xl:top-6">
                <x-ui.field :label="__('Difficulty')" for="difficulty"><x-ui.select name="difficulty">@foreach (['foundation', 'intermediate', 'advanced'] as $d)<option value="{{ $d }}" @selected($lab->difficulty === $d)>{{ __(ucfirst($d)) }}</option>@endforeach</x-ui.select></x-ui.field>
                <x-ui.field :label="__('Estimated minutes')" for="estimated_minutes"><x-ui.input type="number" name="estimated_minutes" :value="$lab->estimated_minutes" min="5" required /></x-ui.field>
                <x-ui.field :label="__('Pass mark (%)')" for="pass_percent"><x-ui.input type="number" name="pass_percent" :value="$lab->pass_percent" min="50" max="100" required /></x-ui.field>
                <x-ui.field :label="__('Required for completion')" for="required"><x-ui.select name="required"><option value="1" @selected($lab->required)>{{ __('Required') }}</option><option value="0" @selected(! $lab->required)>{{ __('Optional') }}</option></x-ui.select></x-ui.field>
                <x-ui.field :label="__('Status')" for="status"><x-ui.select name="status">@foreach (['draft', 'published', 'archived'] as $s)<option value="{{ $s }}" @selected($lab->status === $s)>{{ __(ucfirst($s)) }}</option>@endforeach</x-ui.select></x-ui.field>
                <p class="text-xs text-muted">{{ __('On save the definition is checked and run once through the grader; invalid JSON or mismatched task ids are rejected.') }}</p>
                <x-ui.button class="w-full">{{ __('Save lab') }}</x-ui.button>
            </x-ui.card>
        </div>
    </form>
</x-layouts.admin>
