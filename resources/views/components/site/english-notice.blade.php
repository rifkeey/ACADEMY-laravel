{{--
    Shown on a French page when this item has no approved, current French translation, so the
    English is displayed instead (never a blank page). <x-site.english-notice :item="$lesson" kind="lesson" />
--}}
@props(['item', 'kind' => 'page'])
@if ($item?->shownInEnglish())
    @php
        $messages = [
            'lesson' => __('This lesson is not yet available in French. It is shown in English.'),
            'lab' => __('This lab is not yet available in French. It is shown in English; your answers are marked the same way.'),
            'programme' => __('The French version of this programme is in preparation. Its description is shown in English.'),
            'exam' => __('Some questions in this examination are not yet available in French and will be shown in English.'),
            'page' => __('This content is not yet available in French. It is shown in English.'),
        ];
    @endphp
    <x-ui.alert tone="info" {{ $attributes }}>{{ $messages[$kind] ?? $messages['page'] }}</x-ui.alert>
@endif
