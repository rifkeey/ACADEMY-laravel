{{-- Previous/next pagination for admin tables. <x-admin.pager :items="$users" /> --}}
@props(['items'])
@if ($items->hasPages())
    <nav aria-label="{{ __('Page navigation') }}" class="mt-4 flex items-center justify-between gap-3 text-sm">
        <p class="text-muted">
            @if (method_exists($items, 'total')){{ __('Showing :first–:last of :total', ['first' => $items->firstItem(), 'last' => $items->lastItem(), 'total' => $items->total()]) }}@else {{ __('Page :page', ['page' => $items->currentPage()]) }}@endif
        </p>
        <div class="flex gap-2">
            @if ($items->onFirstPage())<span class="rounded-lg border border-border px-3 py-1.5 text-subtle">{{ __('Previous') }}</span>
            @else<a href="{{ $items->previousPageUrl() }}" rel="prev" class="rounded-lg border border-border-strong px-3 py-1.5 hover:bg-elevated">{{ __('Previous') }}</a>@endif
            @if ($items->hasMorePages())<a href="{{ $items->nextPageUrl() }}" rel="next" class="rounded-lg border border-border-strong px-3 py-1.5 hover:bg-elevated">{{ __('Next') }}</a>
            @else<span class="rounded-lg border border-border px-3 py-1.5 text-subtle">{{ __('Next') }}</span>@endif
        </div>
    </nav>
@endif
