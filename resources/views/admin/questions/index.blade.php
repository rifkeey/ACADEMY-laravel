<x-layouts.admin title="Question bank" description="Only approved questions are drawn into examinations, balanced across categories. Past attempts keep a snapshot of the questions they used.">
    <form method="GET" class="mb-4 flex flex-wrap items-end gap-3">
        <div class="w-64"><x-ui.field label="Bank" for="bank"><x-ui.select name="bank">@foreach ($banks as $b)<option value="{{ $b->id }}" @selected($bank?->id === $b->id)>{{ $b->specialization->title }}</option>@endforeach</x-ui.select></x-ui.field></div>
        <div class="w-40"><x-ui.field label="Status" for="status"><x-ui.select name="status"><option value="">Any</option>@foreach (['draft', 'approved', 'retired'] as $s)<option value="{{ $s }}" @selected($status === $s)>{{ ucfirst($s) }}</option>@endforeach</x-ui.select></x-ui.field></div>
        <div class="w-44"><x-ui.field label="Category" for="category"><x-ui.select name="category"><option value="">Any</option>@foreach ($categories as $c)<option value="{{ $c }}" @selected($category === $c)>{{ $c }}</option>@endforeach</x-ui.select></x-ui.field></div>
        <div class="w-64"><x-ui.field label="Search prompts" for="q"><x-ui.input type="search" name="q" :value="$q" /></x-ui.field></div>
        <x-ui.button variant="secondary">Filter</x-ui.button>
        <x-ui.button :href="route('admin.questions.create', ['bank' => $bank?->id])" class="ml-auto"><x-icon name="plus" class="size-4" /> New question</x-ui.button>
    </form>
    <p class="mb-4 text-sm text-muted">@foreach (['approved', 'draft', 'retired'] as $s){{ ucfirst($s) }}: {{ $counts[$s] ?? 0 }}@if (! $loop->last) · @endif @endforeach</p>

    <form id="bulk" method="POST" action="{{ route('admin.questions.status') }}" class="mb-3 flex flex-wrap items-end gap-3">
        @csrf
        <div class="w-48"><x-ui.field label="Set selected to" for="review_status"><x-ui.select name="review_status">@foreach (['approved', 'draft', 'retired'] as $s)<option value="{{ $s }}">{{ ucfirst($s) }}</option>@endforeach</x-ui.select></x-ui.field></div>
        <x-ui.button variant="secondary">Apply to selected</x-ui.button>
        <p class="text-sm text-muted">Tick the questions you have reviewed. Only approved questions are drawn into exams.</p>
    </form>

    <x-ui.table :head="['Select', 'Question', 'Category', 'Type', 'Difficulty', 'Options', 'Status', '']" caption="Questions">
        @forelse ($questions as $question)
            <tr>
                <x-ui.td><input type="checkbox" form="bulk" name="ids[]" value="{{ $question->id }}" aria-label="Select question {{ $question->id }}" class="size-4 accent-[var(--accent)]"></x-ui.td>
                <x-ui.td class="max-w-xl">{{ \Illuminate\Support\Str::limit($question->prompt, 140) }}</x-ui.td>
                <x-ui.td class="font-mono text-xs">{{ $question->category }}</x-ui.td>
                <x-ui.td>{{ $question->kind === 'single' ? 'Single' : 'Multiple' }}</x-ui.td>
                <x-ui.td>{{ ucfirst($question->difficulty) }}</x-ui.td>
                <x-ui.td class="tabular-nums">{{ $question->options_count }}</x-ui.td>
                <x-ui.td>@include('admin.partials.status', ['value' => $question->review_status])</x-ui.td>
                <x-ui.td><a href="{{ route('admin.questions.edit', $question) }}" class="text-accent-strong hover:underline">Edit</a></x-ui.td>
            </tr>
        @empty
            <tr><x-ui.td colspan="8" class="text-center text-muted">No questions match.</x-ui.td></tr>
        @endforelse
    </x-ui.table>
    <x-admin.pager :items="$questions" />
</x-layouts.admin>
