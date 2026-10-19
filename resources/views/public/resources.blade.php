<x-layouts.site title="Learning resources" description="Free articles on security operations, careers and good practice from AMANIQ Academy.">
    <x-ui.container class="py-14">
        <x-ui.page-header eyebrow="Resources" title="Learning resources" description="Free, original articles from AMANIQ Academy." />
        @if ($articles->isEmpty())
            <div class="mt-10"><x-ui.empty title="No articles yet" icon="book-open">New resources will appear here.</x-ui.empty></div>
        @else
            <div class="mt-10 grid gap-5 md:grid-cols-2 lg:grid-cols-3">
                @foreach ($articles as $a)
                    <x-ui.card as="article" class="relative p-6 hover:border-border-strong">
                        <div class="flex flex-wrap gap-2">@foreach ($a->tags as $t)<x-ui.pill>{{ $t }}</x-ui.pill>@endforeach</div>
                        <h2 class="mt-3 text-lg font-semibold"><a href="{{ route('resources.show', $a) }}" class="after:absolute after:inset-0">{{ $a->title }}</a></h2>
                        <p class="mt-2 text-sm text-muted">{{ $a->summary }}</p>
                        <p class="mt-4 text-xs text-subtle">{{ $a->published_at?->format('j M Y') }}</p>
                    </x-ui.card>
                @endforeach
            </div>
        @endif
    </x-ui.container>
</x-layouts.site>
