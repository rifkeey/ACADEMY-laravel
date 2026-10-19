{{-- Shown after returning from payment. The status comes from the provider via the server, not the redirect. --}}
<x-layouts.site title="Payment status" :noindex="true">
    <x-ui.container class="max-w-2xl py-16">
        @php $total = \App\Support\Money::format($order->total_minor, $order->currency); @endphp
        @if ($status === 'paid')
            <x-ui.alert tone="success" title="Payment confirmed">You are now enrolled in {{ $order->specialization->title }}. A receipt has been recorded under reference {{ $order->reference }}.</x-ui.alert>
            <x-ui.button class="mt-6" :href="route('learn.course', $order->specialization->slug)">Start learning</x-ui.button>
        @elseif ($status === 'pending')
            <x-ui.alert tone="info" title="Waiting for confirmation">We have not received confirmation of your payment of {{ $total }} yet. This usually takes a few moments. Refresh this page shortly; you will not be charged twice.</x-ui.alert>
            <x-ui.button class="mt-6" variant="secondary" :href="route('checkout.complete', ['reference' => $order->reference])">Check again</x-ui.button>
        @elseif ($status === 'refunded')
            <x-ui.alert tone="warning" title="Order refunded">This order ({{ $order->reference }}) was refunded.</x-ui.alert>
        @else
            <x-ui.alert tone="danger" title="Payment not completed">The payment for order {{ $order->reference }} was not successful. If money left your account, contact us and quote this reference. You can try again.</x-ui.alert>
            <x-ui.button class="mt-6" :href="route('checkout.show', $order->specialization)">Try again</x-ui.button>
        @endif
        <p class="mt-8 text-sm text-muted">Order {{ $order->reference }} · {{ $total }} · <a href="{{ route('dashboard.payments') }}" class="text-text underline">Payment history</a></p>
    </x-ui.container>
</x-layouts.site>
