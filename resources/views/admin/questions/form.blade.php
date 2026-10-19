@php
    $editing = $question->exists;
    // Always offer 6 option rows; blank rows are ignored when saving.
    $rows = old('options', $options->map(fn ($o) => ['text' => $o->text, 'correct' => $o->is_correct])->pad(6, ['text' => '', 'correct' => false])->all());
@endphp
<x-layouts.admin :title="$editing ? 'Edit question' : 'New question'" :description="$editing ? $question->bank->specialization->title.' question bank · '.$question->external_key : 'Add a question to a programme bank.'">
    <p class="mb-6 text-sm"><a href="{{ route('admin.questions.index', ['bank' => $question->question_bank_id]) }}" class="text-accent-strong hover:underline">← Back to the question bank</a></p>
    <form method="POST" action="{{ $editing ? route('admin.questions.update', $question) : route('admin.questions.store') }}" class="grid gap-6 xl:grid-cols-[1fr_20rem]">
        @csrf
        @if ($editing) @method('PUT') @endif
        <div class="space-y-5">
            <x-ui.field label="Question" for="prompt"><x-ui.textarea name="prompt" :value="$question->prompt" rows="4" required /></x-ui.field>
            <fieldset class="space-y-2">
                <legend class="text-sm font-medium">Options <span class="font-normal text-subtle">(tick the correct answer or answers)</span></legend>
                @foreach ($rows as $i => $row)
                    <div class="flex items-center gap-3">
                        <input type="hidden" name="options[{{ $i }}][correct]" value="0">
                        <label class="flex items-center gap-1.5 text-xs text-muted"><input type="checkbox" name="options[{{ $i }}][correct]" value="1" @checked(! empty($row['correct'])) class="accent-[var(--accent)]"> Correct</label>
                        <label for="opt-{{ $i }}" class="sr-only">Option {{ $i + 1 }}</label>
                        <input id="opt-{{ $i }}" name="options[{{ $i }}][text]" value="{{ $row['text'] ?? '' }}" maxlength="1000" class="h-10 flex-1 rounded-lg border border-border-strong bg-bg px-3 text-sm" placeholder="Option {{ $i + 1 }}">
                    </div>
                @endforeach
            </fieldset>
            <x-ui.field label="Explanation (shown after the exam if enabled)" for="explanation"><x-ui.textarea name="explanation" :value="$question->explanation" rows="3" /></x-ui.field>
        </div>
        <x-ui.card class="h-fit space-y-4 p-5">
            @unless ($editing)
                <x-ui.field label="Bank" for="question_bank_id"><x-ui.select name="question_bank_id">@foreach ($banks as $b)<option value="{{ $b->id }}" @selected((int) old('question_bank_id', $question->question_bank_id) === $b->id)>{{ $b->specialization->title }}</option>@endforeach</x-ui.select></x-ui.field>
            @endunless
            <x-ui.field label="Answer type" for="kind"><x-ui.select name="kind"><option value="single" @selected(old('kind', $question->kind) === 'single')>Single answer</option><option value="multiple" @selected(old('kind', $question->kind) === 'multiple')>Multiple answers</option></x-ui.select></x-ui.field>
            <x-ui.field label="Category" for="category" hint="Lower-case slug, e.g. triage. Exams balance questions across categories."><x-ui.input name="category" :value="$question->category" required class="font-mono" /></x-ui.field>
            <x-ui.field label="Difficulty" for="difficulty"><x-ui.select name="difficulty">@foreach (['foundation', 'intermediate', 'advanced'] as $d)<option value="{{ $d }}" @selected(old('difficulty', $question->difficulty) === $d)>{{ ucfirst($d) }}</option>@endforeach</x-ui.select></x-ui.field>
            <x-ui.field label="Learning objective" for="objective"><x-ui.input name="objective" :value="$question->objective" /></x-ui.field>
            <x-ui.field label="Review status" for="review_status"><x-ui.select name="review_status">@foreach (['draft', 'approved', 'retired'] as $s)<option value="{{ $s }}" @selected(old('review_status', $question->review_status) === $s)>{{ ucfirst($s) }}</option>@endforeach</x-ui.select></x-ui.field>
            <x-ui.button class="w-full">{{ $editing ? 'Save question' : 'Create question' }}</x-ui.button>
        </x-ui.card>
    </form>
</x-layouts.admin>
