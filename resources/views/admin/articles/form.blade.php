@php $editing = $article->exists; @endphp
<x-layouts.admin :title="$editing ? 'Edit article' : 'New article'">
    <p class="mb-6 text-sm"><a href="{{ route('admin.articles.index') }}" class="text-accent-strong hover:underline">← All articles</a>@if ($editing && $article->status === 'published') · <a href="{{ route('resources.show', $article) }}" class="text-accent-strong hover:underline">View public page</a>@endif</p>
    <form method="POST" action="{{ $editing ? route('admin.articles.update', $article) : route('admin.articles.store') }}" class="grid gap-6 xl:grid-cols-[1fr_20rem]">
        @csrf
        @if ($editing) @method('PUT') @endif
        <div class="space-y-5">
            <x-ui.field label="Title" for="title"><x-ui.input name="title" :value="$article->title" required maxlength="200" /></x-ui.field>
            <x-ui.field label="Summary" for="summary"><x-ui.textarea name="summary" :value="$article->summary" rows="2" required maxlength="400" /></x-ui.field>
            <x-ui.field label="Body (Markdown)" for="body_md" hint="Raw HTML is escaped. Link only to sources you have checked."><x-ui.textarea name="body_md" :value="$article->body_md" rows="26" class="font-mono text-xs" required /></x-ui.field>
        </div>
        <x-ui.card class="h-fit space-y-4 p-5">
            <x-ui.field label="Slug (URL)" for="slug"><x-ui.input name="slug" :value="$article->slug" required pattern="[a-z0-9]+(-[a-z0-9]+)*" class="font-mono" /></x-ui.field>
            <x-ui.field label="Tags (comma separated)" for="tags"><x-ui.input name="tags" :value="implode(', ', $article->tags ?? [])" /></x-ui.field>
            <x-ui.field label="Author" for="author_name"><x-ui.input name="author_name" :value="$article->author_name" required /></x-ui.field>
            <x-ui.field label="Status" for="status" hint="Publishing or unpublishing needs the content.publish permission.">
                <x-ui.select name="status">@foreach (['draft', 'published', 'archived'] as $s)<option value="{{ $s }}" @selected(old('status', $article->status) === $s)>{{ ucfirst($s) }}</option>@endforeach</x-ui.select>
            </x-ui.field>
            <x-ui.button class="w-full">{{ $editing ? 'Save article' : 'Create article' }}</x-ui.button>
        </x-ui.card>
    </form>
</x-layouts.admin>
