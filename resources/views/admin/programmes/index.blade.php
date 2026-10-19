<x-layouts.admin title="Programmes" description="Programme details, publication and course versions. Learners always stay on the version they enrolled in.">
    <x-ui.table :head="['Programme', 'Status', 'Enrolment', 'Live version', 'Draft', 'Price', 'Active learners', '']" caption="Programmes">
        @foreach ($programmes as $p)
            <tr>
                <x-ui.td><p class="font-medium">{{ $p->title }}</p><p class="font-mono text-xs text-subtle">{{ $p->code }} · {{ $p->slug }}</p></x-ui.td>
                <x-ui.td>@include('admin.partials.status', ['value' => $p->status])</x-ui.td>
                <x-ui.td>{{ $p->enrolment_open ? 'Open' : 'Closed' }}</x-ui.td>
                <x-ui.td>{{ $p->currentVersion ? 'v'.$p->currentVersion->version : '—' }}</x-ui.td>
                <x-ui.td>{{ $p->versions->isNotEmpty() ? 'v'.$p->versions->first()->version : '—' }}</x-ui.td>
                <x-ui.td class="tabular-nums">{{ $p->price ? \App\Support\Money::format($p->price->amount_minor, $p->price->currency) : 'Not priced' }}</x-ui.td>
                <x-ui.td class="tabular-nums">{{ $p->enrolments_count }}</x-ui.td>
                <x-ui.td><a href="{{ route('admin.programmes.edit', $p) }}" class="text-accent-strong hover:underline">Manage</a></x-ui.td>
            </tr>
        @endforeach
    </x-ui.table>
</x-layouts.admin>
