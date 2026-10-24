{{-- Shown after returning from payment. The status comes from the provider via the server, not the redirect. --}}
<x-layouts.site :title="__('Payment status')" :noindex="true">
    <x-ui.container class="max-w-2xl py-16">
        @php $total = \App\Support\Money::format($order->total_minor, $order->currency); @endphp
        @if ($status === 'paid')
            <x-ui.alert tone="success" :title="__('Payment confirmed')">{{ __('You are now enrolled in :programme. A receipt has been recorded under reference :reference.', ['programme' => $order->specialization->title, 'reference' => $order->reference]) }}</x-ui.alert>
            <x-ui.button class="mt-6" :href="route('learn.course', $order->specialization->slug)">{{ __('Start learning') }}</x-ui.button>
        @elseif ($status === 'pending')
            <x-ui.alert tone="info" :title="__('Waiting for confirmation')">{{ __('We have not received confirmation of your payment of :total yet. This usually takes a few moments. Refresh this page shortly; you will not be charged twice.', ['total' => $total]) }}</x-ui.alert>
            <x-ui.button class="mt-6" variant="secondary" :href="route('checkout.complete', ['reference' => $order->reference])">{{ __('Check again') }}</x-ui.button>
        @elseif ($status === 'refunded')
            <x-ui.alert tone="warning" :title="__('Order refunded')">{{ __('This order (:reference) was refunded.', ['reference' => $order->reference]) }}</x-ui.alert>
        @else
            <x-ui.alert tone="danger" :title="__('Payment not completed')">{{ __('The payment for order :reference was not successful. If money left your account, contact us and quote this reference. You can try again.', ['reference' => $order->reference]) }}</x-ui.alert>
            <x-ui.button class="mt-6" :href="route('checkout.show', $order->specialization)">{{ __('Try again') }}</x-ui.button>
        @endif
        <p class="mt-8 text-sm text-muted">{{ __('Order :reference', ['reference' => $order->reference]) }} · {{ $total }} · <a href="{{ route('dashboard.payments') }}" class="text-text underline">{{ __('Payment history') }}</a></p>
    </x-ui.container>
</x-layouts.site>
