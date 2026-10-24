<x-layouts.admin :title="__('Examinations')" :description="__('Settings for each course version\'s final examination. The pass mark is stored on every attempt when it starts, so changes never re-grade past attempts.')">
    <div class="space-y-6">
        @forelse ($exams as $exam)
            @php $v = $exam->courseVersion; $stats = $attempts[$exam->id] ?? null; $avail = $approved[$exam->question_bank_id] ?? 0; @endphp
            <x-ui.card class="p-5">
                <div class="flex flex-wrap items-start justify-between gap-3">
                    <div>
                        <h2 class="font-semibold">{{ $v->specialization->title }} <span class="text-sm font-normal text-muted">· {{ __('version :n (:status)', ['n' => $v->version, 'status' => __(ucfirst($v->status))]) }}</span></h2>
                        <p class="mt-1 text-sm text-muted">{{ __(':approved approved questions · :attempts attempts · :passes passes · settings revision :rev', ['approved' => $avail, 'attempts' => $stats->n ?? 0, 'passes' => (int) ($stats->passes ?? 0), 'rev' => $exam->config_version]) }}</p>
                    </div>
                    @include('admin.partials.status', ['value' => $exam->status])
                </div>
                @if ($v->status === 'retired')
                    <p class="mt-4 text-sm text-subtle">{{ __('Retired version: read-only.') }}</p>
                @else
                    <form method="POST" action="{{ route('admin.exams.update', $exam) }}" class="mt-5 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                        @csrf @method('PUT')
                        <div class="sm:col-span-2 lg:col-span-4"><x-ui.field :label="__('Title')" for="t{{ $exam->id }}"><x-ui.input name="title" id="t{{ $exam->id }}" :value="$exam->title" required /></x-ui.field></div>
                        <x-ui.field :label="__('Questions per attempt')" for="qc{{ $exam->id }}"><x-ui.input type="number" name="question_count" id="qc{{ $exam->id }}" :value="$exam->question_count" min="5" max="200" required /></x-ui.field>
                        <x-ui.field :label="__('Pass mark (%)')" for="pp{{ $exam->id }}"><x-ui.input type="number" name="pass_percent" id="pp{{ $exam->id }}" :value="$exam->pass_percent" min="50" max="100" required /></x-ui.field>
                        <x-ui.field :label="__('Time limit (minutes)')" for="d{{ $exam->id }}"><x-ui.input type="number" name="duration_minutes" id="d{{ $exam->id }}" :value="$exam->duration_minutes" min="5" max="480" required /></x-ui.field>
                        <x-ui.field :label="__('Maximum attempts')" for="ma{{ $exam->id }}"><x-ui.input type="number" name="max_attempts" id="ma{{ $exam->id }}" :value="$exam->max_attempts" min="1" max="10" required /></x-ui.field>
                        <x-ui.field :label="__('Hours between attempts')" for="c{{ $exam->id }}"><x-ui.input type="number" name="cooldown_hours" id="c{{ $exam->id }}" :value="$exam->cooldown_hours" min="0" max="720" required /></x-ui.field>
                        <x-ui.field :label="__('Show explanations after submitting')" for="se{{ $exam->id }}"><x-ui.select name="show_explanations" id="se{{ $exam->id }}"><option value="1" @selected($exam->show_explanations)>{{ __('Yes') }}</option><option value="0" @selected(! $exam->show_explanations)>{{ __('No') }}</option></x-ui.select></x-ui.field>
                        <x-ui.field :label="__('Status')" for="s{{ $exam->id }}"><x-ui.select name="status" id="s{{ $exam->id }}"><option value="draft" @selected($exam->status === 'draft')>{{ __('Draft') }}</option><option value="published" @selected($exam->status === 'published')>{{ __('Published') }}</option></x-ui.select></x-ui.field>
                        <div class="flex items-end"><x-ui.button size="sm">{{ __('Save settings') }}</x-ui.button></div>
                    </form>
                @endif
            </x-ui.card>
        @empty
            <x-ui.empty :title="__('No examinations configured')" icon="file-check-2">{{ __('Examinations are created with each course version\'s content.') }}</x-ui.empty>
        @endforelse
    </div>
</x-layouts.admin>
