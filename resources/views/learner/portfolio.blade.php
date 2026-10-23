{{-- Learner portfolio: written deliverables from labs, ready to review, print or save as PDF. --}}
<x-layouts.site title="Your portfolio" :noindex="true">
    <x-ui.container class="py-12">
        <x-ui.page-header eyebrow="Portfolio" title="Your practical work" description="The reports, policies and plans you have written in labs. Each one shows a professional skill you have practised. Print this page or save it as a PDF to share with an employer; it contains no exam answers or account details.">
            <x-slot:actions>
                <div x-data="printPage"><x-ui.button type="button" variant="secondary" @click="print"><x-icon name="file-text" class="size-4" /> Print or save as PDF</x-ui.button></div>
            </x-slot:actions>
        </x-ui.page-header>

        @forelse ($programmes as $p)
            <section class="mt-10" aria-labelledby="pf-{{ $p['spec']->slug }}">
                <h2 id="pf-{{ $p['spec']->slug }}" class="text-xl font-semibold">{{ $p['spec']->title }}</h2>
                <div class="mt-4 space-y-5">
                    @foreach ($p['items'] as $item)
                        <article class="break-inside-avoid rounded-2xl border border-border bg-surface p-6">
                            <div class="flex flex-wrap items-start justify-between gap-3">
                                <div>
                                    <p class="text-xs font-semibold uppercase tracking-wide text-accent">{{ $item['task']['input']['title'] ?? 'Deliverable' }}</p>
                                    <h3 class="mt-1 font-semibold">{{ $item['lab']->title }}</h3>
                                    @if (! empty($item['task']['input']['competency']))<p class="mt-1 text-sm text-muted">Demonstrates: {{ $item['task']['input']['competency'] }}</p>@endif
                                </div>
                                @if ($item['text'] === '')<x-ui.pill>Not started</x-ui.pill>
                                @elseif ($item['complete'])<x-ui.pill tone="success">Complete</x-ui.pill>
                                @else<x-ui.pill tone="warning">Incomplete</x-ui.pill>@endif
                            </div>
                            @if ($item['text'] !== '')
                                <div class="mt-4 whitespace-pre-wrap rounded-lg border border-border bg-bg/40 p-4 text-sm leading-relaxed">{{ $item['text'] }}</div>
                                <p class="mt-2 text-xs text-subtle">Last submitted {{ $item['submittedAt']->format('j M Y, H:i') }} UTC</p>
                            @else
                                <p class="mt-3 text-sm text-muted"><a href="{{ route('learn.lab', [$p['spec']->slug, $item['lab']->slug]) }}" class="text-accent-strong underline">Open the lab</a> to write this deliverable.</p>
                            @endif
                        </article>
                    @endforeach
                </div>
            </section>
        @empty
            <x-ui.empty title="Your portfolio is empty" icon="file-text" class="mt-10">Labs with written deliverables add your work here when you submit them.</x-ui.empty>
        @endforelse
    </x-ui.container>
</x-layouts.site>
