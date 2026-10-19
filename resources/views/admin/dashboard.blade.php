<x-layouts.admin title="Dashboard" description="Overview of the academy. Figures come straight from the database.">
    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <x-ui.stat label="Learner accounts" :value="number_format($stats['learners'])" />
        <x-ui.stat label="Active enrolments" :value="number_format($stats['enrolments'])" />
        <x-ui.stat label="Valid certificates" :value="number_format($stats['certificates'])" />
        <x-ui.stat label="New enquiries" :value="number_format($stats['enquiries'])" />
    </div>

    @can('commerce.manage')
        <section aria-labelledby="rev-h" class="mt-10">
            <h2 id="rev-h" class="font-semibold">Paid orders</h2>
            <div class="mt-3 flex flex-wrap gap-4">
                @forelse ($revenue as $r)
                    <x-ui.stat :label="$r->currency.' revenue ('.$r->orders.' orders)'" :value="\App\Support\Money::format((int) $r->total, $r->currency)" />
                @empty
                    <p class="text-sm text-muted">No paid orders yet.</p>
                @endforelse
            </div>
            @if ($recentOrders->isNotEmpty())
                <div class="mt-4">
                    <x-ui.table :head="['Reference', 'Learner', 'Programme', 'Total', 'Status', 'Created']" caption="Recent orders">
                        @foreach ($recentOrders as $o)
                            <tr>
                                <x-ui.td class="font-mono text-xs">{{ $o->reference }}</x-ui.td>
                                <x-ui.td>{{ $o->user->email }}</x-ui.td>
                                <x-ui.td>{{ $o->specialization->title }}</x-ui.td>
                                <x-ui.td class="tabular-nums">{{ \App\Support\Money::format($o->total_minor, $o->currency) }}</x-ui.td>
                                <x-ui.td>@include('admin.partials.status', ['value' => $o->status])</x-ui.td>
                                <x-ui.td class="whitespace-nowrap text-muted">{{ $o->created_at->format('j M Y H:i') }}</x-ui.td>
                            </tr>
                        @endforeach
                    </x-ui.table>
                </div>
            @endif
        </section>
    @endcan

    @can('audit.read')
        <section aria-labelledby="aud-h" class="mt-10">
            <div class="flex items-center justify-between"><h2 id="aud-h" class="font-semibold">Recent activity</h2><a href="{{ route('admin.audit.index') }}" class="text-sm text-accent-strong hover:underline">Full audit log</a></div>
            <ul class="mt-3 divide-y divide-border rounded-xl border border-border">
                @forelse ($recentAudit as $e)
                    <li class="flex flex-wrap items-center justify-between gap-2 px-4 py-2.5 text-sm">
                        <span><span class="font-mono text-xs text-accent">{{ $e->action }}</span> · {{ $e->entity_type }} {{ $e->entity_id }}</span>
                        <span class="text-muted">{{ $e->actor?->name ?? 'System' }} · {{ $e->created_at->format('j M H:i') }}</span>
                    </li>
                @empty
                    <li class="px-4 py-3 text-sm text-muted">No activity recorded yet.</li>
                @endforelse
            </ul>
        </section>
    @endcan
</x-layouts.admin>
