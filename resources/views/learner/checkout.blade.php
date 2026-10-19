{{-- Checkout: price and any discount are recalculated on the server when the order is created. --}}
<x-layouts.site :title="'Enrol: '.$spec->title" :noindex="true">
    <x-ui.container class="max-w-3xl py-12">
        <x-ui.page-header eyebrow="Enrolment" :title="$spec->title" :description="$spec->summary" />

        <div class="mt-8 space-y-6">
            @if (! $spec->enrolment_open)
                <x-ui.alert tone="info" title="Enrolment is not open yet">This programme is still being prepared. <a href="{{ route('contact') }}" class="font-semibold text-text underline">Contact us</a> to register your interest.</x-ui.alert>
            @elseif (! $price)
                <x-ui.alert tone="warning" title="Not currently priced">Please <a href="{{ route('contact') }}" class="font-semibold text-text underline">contact us</a> to enrol.</x-ui.alert>
            @else
                <x-ui.card class="p-6">
                    <dl class="space-y-3 text-sm">
                        <div class="flex justify-between gap-4"><dt class="text-muted">Programme</dt><dd class="text-right font-medium">{{ $spec->title }}</dd></div>
                        @if ($version)<div class="flex justify-between gap-4"><dt class="text-muted">Course version</dt><dd>{{ $version->version }}</dd></div>@endif
                        <div class="flex justify-between gap-4 border-t border-border pt-3"><dt class="font-semibold">Price</dt><dd class="text-lg font-semibold tabular-nums">{{ \App\Support\Money::format($price->amount_minor, $price->currency) }}</dd></div>
                    </dl>
                </x-ui.card>

                <form method="POST" action="{{ route('checkout.store', $spec) }}" class="space-y-5">
                    @csrf
                    <x-ui.field label="Discount code (optional)" for="discount">
                        <x-ui.input name="discount" id="discount" :value="old('discount')" maxlength="40" autocomplete="off" class="max-w-xs uppercase" />
                    </x-ui.field>
                    <p class="text-sm text-muted">
                        @if ($provider?->id() === 'paystack')
                            You will be taken to Paystack's secure payment page. Your enrolment is activated only after Paystack confirms the payment to us.
                        @elseif ($provider?->id() === 'sandbox')
                            Development mode: payment is simulated and no money is taken.
                        @else
                            Online payment is not configured. Free (100% discounted) enrolments can still be completed.
                        @endif
                        The final amount, after any discount, is shown before you pay. See our <a href="{{ route('legal', 'refunds') }}" class="text-text underline">refund policy</a>.
                    </p>
                    <x-ui.button size="lg">Continue to payment</x-ui.button>
                </form>
            @endif
        </div>
    </x-ui.container>
</x-layouts.site>
