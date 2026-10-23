{{--
    Renders Markdown safely: raw HTML in the source is escaped and unsafe links (javascript:, data:)
    are dropped, so authored content cannot inject scripts. Supports the teaching blocks in
    App\Support\RichMarkdown (:::note, :::reveal …). Pass the text as a prop, not a slot:
    <x-markdown :source="$lesson->body_md" />
--}}
@props(['source' => '', 'compact' => false])
<div {{ $attributes->merge(['class' => $compact ? 'prose-amaniq prose-compact' : 'prose-amaniq']) }}>
    {!! \App\Support\RichMarkdown::render((string) $source) !!}
</div>
