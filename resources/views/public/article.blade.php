<x-layouts.site :title="$article->title" :description="$article->summary">
    <x-ui.container class="py-14">
        <article class="mx-auto max-w-3xl" @if ($article->shownInEnglish()) lang="en" @endif>
            <a href="{{ lroute('resources.index') }}" class="text-sm text-subtle hover:text-text">← {{ __('All resources') }}</a>
            <h1 class="mt-4 text-4xl font-semibold tracking-tight">{{ $article->title }}</h1>
            <p class="mt-3 text-lg text-muted">{{ $article->summary }}</p>
            <x-site.english-notice :item="$article" class="mt-4" />
            <p class="mt-4 text-sm text-subtle">{{ $article->author_name }} · {{ $article->published_at?->translatedFormat('j M Y') }}</p>
            <x-markdown class="mt-10" :source="$article->body_md" />
        </article>
    </x-ui.container>
</x-layouts.site>
