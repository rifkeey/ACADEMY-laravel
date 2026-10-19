{{-- Responsive table. <x-ui.table :head="['Name', 'Date']" caption="..."> rows... </x-ui.table> --}}
@props(['head' => [], 'caption' => null])
<div class="overflow-x-auto rounded-xl border border-border">
    <table class="w-full text-left text-sm">
        @if ($caption)<caption class="sr-only">{{ $caption }}</caption>@endif
        <thead class="bg-elevated text-xs uppercase tracking-wide text-muted">
            <tr>@foreach ($head as $h)<th scope="col" class="whitespace-nowrap px-4 py-3 font-semibold">{{ $h }}</th>@endforeach</tr>
        </thead>
        <tbody class="divide-y divide-border">{{ $slot }}</tbody>
    </table>
</div>
