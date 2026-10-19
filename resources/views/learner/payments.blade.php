<x-layouts.site title="Payment history" :noindex="true">
    @php $tones = ['paid' => 'success', 'pending' => 'warning', 'failed' => 'danger', 'cancelled' => 'neutral', 'refunded' => 'blue']; @endphp
    <x-ui.container class="py-12">
        <x-ui.page-header eyebrow="Account" title="Payment history" description="Your orders and receipts. Payments are processed by our payment provider; we never store card details." />
        <div class="mt-8">
            @if ($orders->isEmpty())
                <x-ui.empty title="No payments yet" icon="receipt">Orders you place will appear here.</x-ui.empty>
            @else
                <x-ui.table :head="['Date', 'Programme', 'Reference', 'Amount', 'Status']" caption="Your orders">
                    @foreach ($orders as $o)
                        <tr>
                            <x-ui.td>{{ $o->created_at->format('j M Y') }}</x-ui.td>
                            <x-ui.td>{{ $o->specialization->title }}</x-ui.td>
                            <x-ui.td class="font-mono text-xs">{{ $o->reference }}</x-ui.td>
                            <x-ui.td class="tabular-nums">{{ \App\Support\Money::format($o->total_minor, $o->currency) }}@if ($o->discount_minor > 0)<span class="block text-xs text-subtle">incl. discount {{ \App\Support\Money::format($o->discount_minor, $o->currency) }}</span>@endif</x-ui.td>
                            <x-ui.td><x-ui.pill :tone="$tones[$o->status] ?? 'neutral'">{{ $o->status }}</x-ui.pill></x-ui.td>
                        </tr>
                    @endforeach
                </x-ui.table>
            @endif
        </div>
    </x-ui.container>
</x-layouts.site>
