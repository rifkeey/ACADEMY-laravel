{{-- Development-only stand-in for the payment provider's hosted page. Refused in production. --}}
<x-layouts.site :title="__('Sandbox payment')" :noindex="true">
    <x-ui.container class="max-w-xl py-16">
        <x-ui.alert tone="warning" :title="__('Sandbox payment (development only)')">{{ __('No real payment happens here. Choose an outcome to simulate the provider\'s response and signed webhook.') }}</x-ui.alert>
        <x-ui.card class="mt-6 p-6">
            <dl class="space-y-2 text-sm">
                <div class="flex justify-between"><dt class="text-muted">{{ __('Reference') }}</dt><dd class="font-mono">{{ $order->reference }}</dd></div>
                <div class="flex justify-between"><dt class="text-muted">{{ __('Subtotal') }}</dt><dd class="tabular-nums">{{ \App\Support\Money::format($order->subtotal_minor, $order->currency) }}</dd></div>
                @if ($order->discount_minor > 0)<div class="flex justify-between"><dt class="text-muted">{{ __('Discount') }}</dt><dd class="tabular-nums">−{{ \App\Support\Money::format($order->discount_minor, $order->currency) }}</dd></div>@endif
                <div class="flex justify-between border-t border-border pt-2 font-semibold"><dt>{{ __('Total') }}</dt><dd class="tabular-nums">{{ \App\Support\Money::format($order->total_minor, $order->currency) }}</dd></div>
            </dl>
            <form method="POST" action="{{ route('checkout.sandbox.pay', $order->reference) }}" class="mt-6 flex flex-wrap gap-3">
                @csrf
                <x-ui.button name="outcome" value="success">{{ __('Simulate successful payment') }}</x-ui.button>
                <x-ui.button name="outcome" value="failed" variant="secondary">{{ __('Simulate failed payment') }}</x-ui.button>
            </form>
        </x-ui.card>
    </x-ui.container>
</x-layouts.site>
