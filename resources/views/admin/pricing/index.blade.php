<x-layouts.admin title="Pricing and discounts" description="Prices are stored exactly in kobo. Changing a price starts a new offer; orders already created keep the price they were quoted.">
    <section aria-labelledby="price-h">
        <h2 id="price-h" class="text-lg font-semibold">Programme prices (NGN)</h2>
        <div class="mt-4 grid gap-4 md:grid-cols-2 xl:grid-cols-3">
            @foreach ($programmes as $p)
                <x-ui.card class="p-5">
                    <p class="font-medium">{{ $p->title }}</p>
                    <p class="mt-1 text-2xl font-semibold tabular-nums">{{ $p->price ? \App\Support\Money::format($p->price->amount_minor, $p->price->currency) : 'Not priced' }}</p>
                    @if ($p->price)<p class="text-xs text-subtle">Since {{ $p->price->valid_from->format('j M Y') }}</p>@endif
                    <form method="POST" action="{{ route('admin.pricing.price', $p) }}" class="mt-4 flex items-end gap-2">
                        @csrf
                        <div class="flex-1"><label for="amt-{{ $p->id }}" class="text-xs text-muted">New price in naira</label>
                            <input id="amt-{{ $p->id }}" name="amount" inputmode="decimal" required pattern="\d{1,9}(\.\d{1,2})?" placeholder="e.g. 180000" class="h-9 w-full rounded-lg border border-border-strong bg-bg px-3 text-sm tabular-nums"></div>
                        <x-ui.button size="sm" variant="secondary">Set price</x-ui.button>
                    </form>
                </x-ui.card>
            @endforeach
        </div>
    </section>

    <section aria-labelledby="disc-h" class="mt-10">
        <h2 id="disc-h" class="text-lg font-semibold">Discount codes</h2>
        <form method="POST" action="{{ route('admin.discounts.store') }}" class="mt-4 grid gap-4 rounded-2xl border border-border bg-surface p-5 sm:grid-cols-2 lg:grid-cols-4">
            @csrf
            <x-ui.field label="Code" for="code"><x-ui.input name="code" required class="font-mono uppercase" maxlength="40" /></x-ui.field>
            <x-ui.field label="Type" for="type"><x-ui.select name="type"><option value="percent" @selected(old('type') !== 'amount')>Percentage off</option><option value="amount" @selected(old('type') === 'amount')>Fixed amount off</option></x-ui.select></x-ui.field>
            <x-ui.field label="Percent off (if percentage)" for="percent_off"><x-ui.input type="number" name="percent_off" min="1" max="100" /></x-ui.field>
            <x-ui.field label="Naira off (if fixed)" for="amount_off"><x-ui.input name="amount_off" inputmode="decimal" /></x-ui.field>
            <x-ui.field label="Programme" for="specialization_id"><x-ui.select name="specialization_id"><option value="">All programmes</option>@foreach ($programmes as $p)<option value="{{ $p->id }}" @selected((int) old('specialization_id') === $p->id)>{{ $p->title }}</option>@endforeach</x-ui.select></x-ui.field>
            <x-ui.field label="Maximum uses" for="max_redemptions"><x-ui.input type="number" name="max_redemptions" min="1" /></x-ui.field>
            <x-ui.field label="Expires" for="valid_until"><x-ui.input type="datetime-local" name="valid_until" /></x-ui.field>
            <x-ui.field label="Internal description" for="description"><x-ui.input name="description" maxlength="200" /></x-ui.field>
            <div><x-ui.button size="sm">Create code</x-ui.button></div>
        </form>

        <div class="mt-4">
            <x-ui.table :head="['Code', 'Discount', 'Programme', 'Used', 'Expires', 'Status', '']" caption="Discount codes">
                @forelse ($discounts as $d)
                    <tr>
                        <x-ui.td><p class="font-mono">{{ $d->code }}</p>@if ($d->description)<p class="text-xs text-muted">{{ $d->description }}</p>@endif</x-ui.td>
                        <x-ui.td class="tabular-nums">{{ $d->percent_off ? $d->percent_off.'%' : \App\Support\Money::format((int) $d->amount_off_minor, $d->currency ?? 'NGN') }}</x-ui.td>
                        <x-ui.td>{{ $programmes->firstWhere('id', $d->specialization_id)?->title ?? 'All' }}</x-ui.td>
                        <x-ui.td class="tabular-nums">{{ $d->redemptions }}{{ $d->max_redemptions ? ' / '.$d->max_redemptions : '' }}</x-ui.td>
                        <x-ui.td class="whitespace-nowrap">{{ $d->valid_until?->format('j M Y H:i') ?? 'Never' }}</x-ui.td>
                        <x-ui.td>@include('admin.partials.status', ['value' => $d->active ? 'active' : 'cancelled'])</x-ui.td>
                        <x-ui.td>
                            <form method="POST" action="{{ route('admin.discounts.toggle', $d) }}">@csrf @method('PUT')<input type="hidden" name="active" value="{{ $d->active ? 0 : 1 }}"><x-ui.button variant="ghost" size="sm">{{ $d->active ? 'Disable' : 'Enable' }}</x-ui.button></form>
                        </x-ui.td>
                    </tr>
                @empty
                    <tr><x-ui.td colspan="7" class="text-center text-muted">No discount codes yet.</x-ui.td></tr>
                @endforelse
            </x-ui.table>
        </div>
    </section>

    <section aria-labelledby="hist-h" class="mt-10">
        <h2 id="hist-h" class="text-lg font-semibold">Price history</h2>
        <div class="mt-4">
            <x-ui.table :head="['Programme', 'Price', 'From', 'Until', 'Active']" caption="Price history">
                @foreach ($history as $o)
                    <tr>
                        <x-ui.td>{{ $o->specialization->title }}</x-ui.td>
                        <x-ui.td class="tabular-nums">{{ \App\Support\Money::format($o->amount_minor, $o->currency) }}</x-ui.td>
                        <x-ui.td class="whitespace-nowrap">{{ $o->valid_from->format('j M Y H:i') }}</x-ui.td>
                        <x-ui.td class="whitespace-nowrap">{{ $o->valid_until?->format('j M Y H:i') ?? '—' }}</x-ui.td>
                        <x-ui.td>{{ $o->active ? 'Yes' : 'No' }}</x-ui.td>
                    </tr>
                @endforeach
            </x-ui.table>
        </div>
    </section>
</x-layouts.admin>
