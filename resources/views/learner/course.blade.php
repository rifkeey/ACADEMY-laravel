<x-layouts.site :title="$spec->title" :noindex="true">
    @php $r = $requirements; @endphp
    <x-ui.container class="py-12">
        <x-ui.page-header :eyebrow="__('Course version :n', ['n' => $version->version])" :title="$spec->title" :description="__(':done of :total required lessons and :passed of :labs labs complete.', ['done' => $r['lessonsDone'], 'total' => $r['lessonsTotal'], 'passed' => $r['labsPassed'], 'labs' => $r['labsTotal']])">
            @if ($nextLesson)
                <x-slot:actions><x-ui.button :href="route('learn.lesson', [$slug, $nextLesson->id])">{{ __('Continue learning') }}</x-ui.button></x-slot:actions>
            @endif
        </x-ui.page-header>

        <div class="mt-8 grid gap-8 lg:grid-cols-[1fr_20rem]">
            <ol class="space-y-4">
                @foreach ($modules as $m)
                    <li>
                        <x-ui.card class="p-5">
                            <div class="flex flex-wrap items-start justify-between gap-2">
                                <div><p class="text-xs font-semibold uppercase tracking-wide text-accent">Module {{ $m->position }}</p><h2 class="mt-1 font-semibold">{{ $m->title }}</h2></div>
                                <span class="text-xs text-muted">{{ __(':done/:total lessons', ['done' => $m->lessons->where('done', true)->count(), 'total' => $m->lessons->count()]) }}</span>
                            </div>
                            <ul class="mt-4 space-y-1">
                                @foreach ($m->lessons as $l)
                                    <li><a href="{{ route('learn.lesson', [$slug, $l->id]) }}" class="flex items-center gap-3 rounded-lg px-2 py-2 text-sm hover:bg-elevated">
                                        <x-icon :name="$l->done ? 'check-circle-2' : 'circle'" :label="$l->done ? __('Completed') : __('Not completed')" class="size-4 {{ $l->done ? 'text-success' : 'text-subtle' }}" />
                                        <span class="flex-1">{{ $l->title }}</span><span class="text-xs text-subtle">{{ __(':n min', ['n' => $l->estimated_minutes]) }}</span>
                                    </a></li>
                                @endforeach
                                @foreach ($m->labs as $l)
                                    <li><a href="{{ route('learn.lab', [$slug, $l->slug]) }}" class="flex items-center gap-3 rounded-lg px-2 py-2 text-sm hover:bg-elevated">
                                        <x-icon name="flask-conical" class="size-4 {{ $l->passed ? 'text-success' : 'text-accent' }}" />
                                        <span class="flex-1">{{ __('Lab: :title', ['title' => $l->title]) }}</span>
                                        @if ($l->passed)<x-ui.pill tone="success">{{ __('Passed') }}</x-ui.pill>@else<x-ui.pill>{{ __('Lab') }}</x-ui.pill>@endif
                                    </a></li>
                                @endforeach
                                @if ($m->lessons->isEmpty() && $m->labs->isEmpty())<li class="px-2 text-sm text-subtle">{{ __('Content for this module is in development.') }}</li>@endif
                            </ul>
                        </x-ui.card>
                    </li>
                @endforeach
            </ol>

            <aside class="space-y-4">
                <x-ui.card class="space-y-4 p-5">
                    <h2 class="font-semibold">{{ __('Completion requirements') }}</h2>
                    <x-ui.progress :label="__('Required lessons')" :value="$r['lessonsTotal'] ? $r['lessonsDone'] / $r['lessonsTotal'] * 100 : 0" />
                    <x-ui.progress :label="__('Required labs')" :value="$r['labsTotal'] ? $r['labsPassed'] / $r['labsTotal'] * 100 : 100" />
                    <div class="flex items-center gap-2 text-sm">
                        <x-icon :name="$r['examPassed'] ? 'check-circle-2' : ($r['examEligible'] ? 'file-check-2' : 'lock')" class="size-4 {{ $r['examPassed'] ? 'text-success' : ($r['examEligible'] ? 'text-accent' : 'text-subtle') }}" />
                        {{ $r['examPassed'] ? __('Final examination: passed') : ($r['examEligible'] ? __('Final examination: unlocked') : ($r['examPublished'] ? __('Final examination: locked until lessons and labs are complete') : __('Final examination: not yet published'))) }}
                    </div>
                    @if ($r['examPublished'])
                        <x-ui.button :href="route('exam.show', $slug)" :variant="$r['examEligible'] && ! $r['examPassed'] ? 'primary' : 'secondary'" size="sm" class="w-full">{{ $r['examPassed'] ? __('View examination results') : __('Examination details') }}</x-ui.button>
                    @endif
                </x-ui.card>
                @if ($certificate)
                    <x-ui.alert tone="success" :title="__('Programme complete')">{{ __('Your certificate :number has been issued.', ['number' => $certificate->certificate_number]) }} <a href="{{ route('certificates.pdf', $certificate) }}" class="font-semibold text-text underline">{{ __('Download PDF') }}</a></x-ui.alert>
                @endif
            </aside>
        </div>
    </x-ui.container>
</x-layouts.site>
