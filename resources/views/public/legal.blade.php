<x-layouts.site :title="$doc['title']" :description="$doc['summary']">
    <x-ui.container class="py-14">
        <article class="mx-auto max-w-3xl">
            <h1 class="text-4xl font-semibold tracking-tight">{{ $doc['title'] }}</h1>
            <p class="mt-3 text-lg text-muted">{{ $doc['summary'] }}</p>
            <x-ui.alert tone="warning" :title="__('Draft for legal review')" class="mt-6">{{ __('This policy is a working draft. Text in [square brackets] must be confirmed by AMANIQ Projects Limited before launch.') }}</x-ui.alert>
            <x-markdown class="mt-10" :source="$doc['body']" />
        </article>
    </x-ui.container>
</x-layouts.site>
