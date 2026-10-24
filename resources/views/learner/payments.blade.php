<x-layouts.site :title="__('Payment history')" :noindex="true">
    @php $tones = ['paid' => 'success', 'pending' => 'warning', 'failed' => 'danger', 'cancelled' => 'neutral', 'refunded' => 'blue']; @endphp
    <x-ui.container class="py-12">
        <x-ui.page-header :eyebrow="__('Account')" :title="__('Payment history')" :description="__('Your orders and receipts. Payments are processed by our payment provider; we never store card details.')" />
        <div class="mt-8">
            @if ($orders->isEmpty())
                <x-ui.empty :title="__('No payments yet')" icon="receipt">{{ __('Orders you place will appear here.') }}</x-ui.empty>
            @else
                <x-ui.table :head="[__('Date'), __('Programme'), __('Reference'), __('Amount'), __('Status')]" :caption="__('Your orders')">
                    @foreach ($orders as $o)
                        <tr>
                            <x-ui.td>{{ $o->created_at->translatedFormat('j M Y') }}</x-ui.td>
                            <x-ui.td>{{ $o->specialization->title }}</x-ui.td>
                            <x-ui.td class="font-mono text-xs">{{ $o->reference }}</x-ui.td>
                            <x-ui.td class="tabular-nums">{{ \App\Support\Money::format($o->total_minor, $o->currency) }}@if ($o->discount_minor > 0)<span class="block text-xs text-subtle">{{ __('incl. discount :amount', ['amount' => \App\Support\Money::format($o->discount_minor, $o->currency)]) }}</span>@endif</x-ui.td>
                            <x-ui.td><x-ui.pill :tone="$tones[$o->status] ?? 'neutral'">{{ __(ucfirst($o->status)) }}</x-ui.pill></x-ui.td>
                        </tr>
                    @endforeach
                </x-ui.table>
            @endif
        </div>
    </x-ui.container>
</x-layouts.site>
