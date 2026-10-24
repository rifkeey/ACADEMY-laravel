<x-layouts.admin :title="__('Orders and enrolments')" :description="__('Payments are confirmed with the provider before access is granted. Refunds are made in the provider\'s dashboard first, then recorded here, which removes access.')">
    <form method="GET" class="mb-6 flex flex-wrap items-end gap-3">
        <div class="w-72"><x-ui.field :label="__('Order reference or learner email')" for="q"><x-ui.input type="search" name="q" :value="$q" /></x-ui.field></div>
        @if ($orders)
            <div class="w-40"><x-ui.field :label="__('Order status')" for="status"><x-ui.select name="status"><option value="">{{ __('Any') }}</option>@foreach (['pending', 'paid', 'failed', 'cancelled', 'refunded'] as $s)<option value="{{ $s }}" @selected($status === $s)>{{ __(ucfirst($s)) }}</option>@endforeach</x-ui.select></x-ui.field></div>
        @endif
        <x-ui.button variant="secondary">{{ __('Filter') }}</x-ui.button>
    </form>

    @if ($orders)
        <section aria-labelledby="ord-h">
            <h2 id="ord-h" class="text-lg font-semibold">{{ __('Orders') }}</h2>
            <p class="mt-1 text-sm text-muted">
                {{ __('Online payment by currency:') }}
                @foreach ($paymentRoutes as $code => $providerId)
                    <span class="ml-2 whitespace-nowrap"><span class="font-semibold">{{ \App\Support\Currency::short($code) }}</span> → {{ $providerId ?? __('off (contact us)') }}</span>
                @endforeach
            </p>
            <div class="mt-3">
                <x-ui.table :head="[__('Reference'), __('Learner'), __('Programme'), __('Total'), __('Payment'), __('Status'), __('Created'), '']" :caption="__('Orders')">
                    @forelse ($orders as $o)
                        @php $pay = $o->payments->first(); @endphp
                        <tr>
                            <x-ui.td class="font-mono text-xs">{{ $o->reference }}</x-ui.td>
                            <x-ui.td>{{ $o->user->email }}</x-ui.td>
                            <x-ui.td>{{ $o->specialization->title }}</x-ui.td>
                            <x-ui.td class="whitespace-nowrap tabular-nums">{{ \App\Support\Money::format($o->total_minor, $o->currency) }}@if ($o->discount_minor > 0)<p class="text-xs text-subtle">{{ __('−:amount discount', ['amount' => \App\Support\Money::format($o->discount_minor, $o->currency)]) }}</p>@endif</x-ui.td>
                            <x-ui.td class="text-xs text-muted">{{ $o->provider }}@if ($pay || $o->provider_reference)<br><span class="font-mono">{{ $pay?->provider_reference ?? $o->provider_reference }}</span>@endif</x-ui.td>
                            <x-ui.td>@include('admin.partials.status', ['value' => $o->status])</x-ui.td>
                            <x-ui.td class="whitespace-nowrap text-muted">{{ $o->created_at->translatedFormat('j M Y H:i') }}</x-ui.td>
                            <x-ui.td>
                                @if ($o->status === 'pending' && $o->provider !== 'none')
                                    <form method="POST" action="{{ route('admin.orders.check', $o) }}">@csrf<x-ui.button variant="ghost" size="sm">{{ __('Check with provider') }}</x-ui.button></form>
                                @endif
                                @if ($o->status === 'paid')
                                    <x-ui.confirm :action="route('admin.orders.refund', $o)" :label="__('Record refund')" :title="__('Record a refund for :reference?', ['reference' => $o->reference])" :description="__('Only do this after refunding in the payment provider\'s dashboard. The learner\'s enrolment is revoked.')" :confirm-label="__('Record refund')" :require-reason="true" />
                                @endif
                            </x-ui.td>
                        </tr>
                    @empty
                        <tr><x-ui.td colspan="8" class="text-center text-muted">{{ __('No orders found.') }}</x-ui.td></tr>
                    @endforelse
                </x-ui.table>
            </div>
            <x-admin.pager :items="$orders" />
        </section>
    @endif

    @if ($enrolments)
        <section aria-labelledby="enr-h" class="mt-10">
            <h2 id="enr-h" class="text-lg font-semibold">{{ __('Enrolments') }}</h2>
            <form method="POST" action="{{ route('admin.enrolments.grant') }}" class="mt-4 grid gap-4 rounded-2xl border border-border bg-surface p-5 sm:grid-cols-2 lg:grid-cols-4">
                @csrf
                <x-ui.field :label="__('Learner email (account must exist)')" for="email"><x-ui.input type="email" name="email" required /></x-ui.field>
                <x-ui.field :label="__('Programme')" for="specialization_id"><x-ui.select name="specialization_id">@foreach ($programmes as $p)<option value="{{ $p->id }}">{{ $p->title }}</option>@endforeach</x-ui.select></x-ui.field>
                <x-ui.field :label="__('Reason (scholarship, staff, corporate pilot…)')" for="note"><x-ui.input name="note" required minlength="5" maxlength="300" /></x-ui.field>
                <div class="flex items-end"><x-ui.button size="sm">{{ __('Grant complimentary enrolment') }}</x-ui.button></div>
            </form>
            <div class="mt-4">
                <x-ui.table :head="[__('Learner'), __('Programme'), __('Source'), __('Enrolled'), __('Status'), '']" :caption="__('Enrolments')">
                    @forelse ($enrolments as $e)
                        <tr>
                            <x-ui.td><p>{{ $e->user->name }}</p><p class="text-xs text-muted">{{ $e->user->email }}</p></x-ui.td>
                            <x-ui.td>{{ $e->specialization->title }} <span class="text-xs text-subtle">v{{ $e->courseVersion->version }}</span></x-ui.td>
                            <x-ui.td>{{ __(ucfirst($e->source)) }}</x-ui.td>
                            <x-ui.td class="whitespace-nowrap">{{ $e->enrolled_at->translatedFormat('j M Y') }}@if ($e->completed_at)<p class="text-xs text-success">{{ __('Completed :date', ['date' => $e->completed_at->translatedFormat('j M Y')]) }}</p>@endif</x-ui.td>
                            <x-ui.td>@include('admin.partials.status', ['value' => $e->status])</x-ui.td>
                            <x-ui.td>
                                @if ($e->status === 'active')
                                    <x-ui.confirm :action="route('admin.enrolments.revoke', $e)" :label="__('Revoke')" :title="__('Revoke this enrolment?')" :description="__('The learner loses access to the course. Progress is kept.')" :confirm-label="__('Revoke enrolment')" :require-reason="true" />
                                @endif
                            </x-ui.td>
                        </tr>
                    @empty
                        <tr><x-ui.td colspan="6" class="text-center text-muted">{{ __('No enrolments found.') }}</x-ui.td></tr>
                    @endforelse
                </x-ui.table>
            </div>
            <x-admin.pager :items="$enrolments" />
        </section>
    @endif
</x-layouts.admin>
