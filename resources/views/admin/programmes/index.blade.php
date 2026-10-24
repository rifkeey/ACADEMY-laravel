<x-layouts.admin :title="__('Programmes')" :description="__('Programme details, publication and course versions. Learners always stay on the version they enrolled in.')">
    <x-ui.table :head="[__('Programme'), __('Status'), __('Enrolment'), __('Live version'), __('Draft'), __('Price'), __('Active learners'), '']" :caption="__('Programmes')">
        @foreach ($programmes as $p)
            <tr>
                <x-ui.td><p class="font-medium">{{ $p->title }}</p><p class="font-mono text-xs text-subtle">{{ $p->code }} · {{ $p->slug }}</p></x-ui.td>
                <x-ui.td>@include('admin.partials.status', ['value' => $p->status])</x-ui.td>
                <x-ui.td>{{ $p->enrolment_open ? __('Open') : __('Closed') }}</x-ui.td>
                <x-ui.td>{{ $p->currentVersion ? 'v'.$p->currentVersion->version : '—' }}</x-ui.td>
                <x-ui.td>{{ $p->versions->isNotEmpty() ? 'v'.$p->versions->first()->version : '—' }}</x-ui.td>
                <x-ui.td class="tabular-nums">{{ $p->price ? \App\Support\Money::format($p->price->amount_minor, $p->price->currency) : __('Not priced') }}</x-ui.td>
                <x-ui.td class="tabular-nums">{{ $p->enrolments_count }}</x-ui.td>
                <x-ui.td><a href="{{ route('admin.programmes.edit', $p) }}" class="text-accent-strong hover:underline">{{ __('Manage') }}</a></x-ui.td>
            </tr>
        @endforeach
    </x-ui.table>
</x-layouts.admin>
