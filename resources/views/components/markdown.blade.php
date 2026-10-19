{{--
    Renders Markdown safely: raw HTML in the source is escaped and unsafe links (javascript:, data:)
    are dropped, so authored content cannot inject scripts. Pass the text as a prop, not a slot:
    <x-markdown :source="$lesson->body_md" />
--}}
@props(['source' => '', 'compact' => false])
<div {{ $attributes->merge(['class' => $compact ? 'prose-amaniq prose-compact' : 'prose-amaniq']) }}>
    {!! \Illuminate\Support\Str::markdown((string) $source, ['html_input' => 'escape', 'allow_unsafe_links' => false]) !!}
</div>
