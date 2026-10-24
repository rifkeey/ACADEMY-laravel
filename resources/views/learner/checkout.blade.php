{{-- Checkout: price and any discount are recalculated on the server when the order is created. --}}
<x-layouts.site :title="__('Enrol: :programme', ['programme' => $spec->title])" :noindex="true">
    <x-ui.container class="max-w-3xl py-12">
        <x-ui.page-header :eyebrow="__('Enrolment')" :title="$spec->title" :description="$spec->summary" />

        <div class="mt-8 space-y-6">
            @if (! $spec->enrolment_open)
                <x-ui.alert tone="info" :title="__('Enrolment is not open yet')">{{ __('This programme is still being prepared.') }} {!! __(':link to register your interest.', ['link' => '<a href="'.e(lroute('contact')).'" class="font-semibold text-text underline">'.e(__('Contact us')).'</a>']) !!}</x-ui.alert>
            @elseif (! $price && $prices->isNotEmpty())
                {{-- Priced in another currency only: never charge that one silently. --}}
                @php $other = $prices->keys()->first(); @endphp
                <x-ui.alert tone="warning" :title="__('Price in :currency coming soon', ['currency' => \App\Support\Currency::short(\App\Support\Currency::current())])">{!! __('A price in :currency has not been set for this programme yet. :contact, or :switch.', [
                    'currency' => e(\App\Support\Currency::short(\App\Support\Currency::current())),
                    'contact' => '<a href="'.e(lroute('contact', ['kind' => 'programme_interest', 'programme' => $spec->slug])).'" class="font-semibold text-text underline">'.e(__('Contact us to enrol')).'</a>',
                    'switch' => '<a href="'.e(route('currency.switch', ['currency' => $other, 'to' => request()->getRequestUri()])).'" class="text-text underline">'.e(__('view the price in :other', ['other' => \App\Support\Currency::short($other)])).'</a>',
                ]) !!}</x-ui.alert>
            @elseif (! $price)
                <x-ui.alert tone="warning" :title="__('Not currently priced')">{!! __('Please :link to enrol.', ['link' => '<a href="'.e(lroute('contact')).'" class="font-semibold text-text underline">'.e(__('contact us')).'</a>']) !!}</x-ui.alert>
            @else
                @if ($provider && ! $provider->supportsCurrency($price->currency))
                    <x-ui.alert tone="warning" :title="__('Online payment in :currency is not available yet', ['currency' => \App\Support\Currency::short($price->currency)])">{!! __('Please :link to enrol. Your price is shown below.', ['link' => '<a href="'.e(lroute('contact', ['kind' => 'programme_interest', 'programme' => $spec->slug])).'" class="font-semibold text-text underline">'.e(__('contact us')).'</a>']) !!}</x-ui.alert>
                @endif
                <x-ui.card class="p-6">
                    <dl class="space-y-3 text-sm">
                        <div class="flex justify-between gap-4"><dt class="text-muted">{{ __('Programme') }}</dt><dd class="text-right font-medium">{{ $spec->title }}</dd></div>
                        @if ($version)<div class="flex justify-between gap-4"><dt class="text-muted">{{ __('Course version') }}</dt><dd>{{ $version->version }}</dd></div>@endif
                        <div class="flex justify-between gap-4 border-t border-border pt-3"><dt class="font-semibold">{{ __('Price') }}</dt><dd class="text-lg font-semibold tabular-nums">{{ \App\Support\Money::format($price->amount_minor, $price->currency) }}</dd></div>
                    </dl>
                    <x-site.currency-switch class="mt-4" />
                </x-ui.card>

                <form method="POST" action="{{ route('checkout.store', $spec) }}" class="space-y-5">
                    @csrf
                    <x-ui.field :label="__('Discount code (optional)')" for="discount">
                        <x-ui.input name="discount" id="discount" :value="old('discount')" maxlength="40" autocomplete="off" class="max-w-xs uppercase" />
                    </x-ui.field>
                    <p class="text-sm text-muted">
                        @if ($provider?->id() === 'paystack')
                            {{ __('You will be taken to Paystack\'s secure payment page. Your enrolment is activated only after Paystack confirms the payment to us.') }}
                        @elseif ($provider?->id() === 'sandbox')
                            {{ __('Development mode: payment is simulated and no money is taken.') }}
                        @else
                            {{ __('Online payment is not configured. Free (100% discounted) enrolments can still be completed.') }}
                        @endif
                        {!! __('The final amount, after any discount, is shown before you pay. See our :link.', ['link' => '<a href="'.e(lroute('legal', 'refunds')).'" class="text-text underline">'.e(__('refund policy')).'</a>']) !!}
                    </p>
                    <x-ui.button size="lg">{{ __('Continue to payment') }}</x-ui.button>
                </form>
            @endif
        </div>
    </x-ui.container>
</x-layouts.site>
