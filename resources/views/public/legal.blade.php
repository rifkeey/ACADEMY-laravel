<x-layouts.site :title="$page->title" :description="$page->summary">
    <x-ui.container class="py-14">
        <article class="mx-auto max-w-3xl" @if ($page->shownInEnglish()) lang="en" @endif>
            <h1 class="text-4xl font-semibold tracking-tight">{{ $page->title }}</h1>
            <p class="mt-3 text-lg text-muted">{{ $page->summary }}</p>
            <x-site.english-notice :item="$page" class="mt-6" />
            <x-ui.alert tone="warning" :title="__('Draft for legal review')" class="mt-6">{{ __('This policy is a working draft. Text in [square brackets] must be confirmed by AMANIQ Projects Limited before launch.') }}</x-ui.alert>
            <x-markdown class="mt-10" :source="$page->body_md" />
        </article>
    </x-ui.container>
</x-layouts.site>
