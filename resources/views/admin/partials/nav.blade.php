{{-- Admin side navigation (shared by the desktop sidebar and the phone menu). --}}
<nav aria-label="Admin" class="space-y-5">
    @foreach ($groups as $group => $items)
        @php $visible = array_filter($items, fn ($i) => $can($i[3])); @endphp
        @if ($visible)
            <div>
                <p class="px-2 text-xs font-semibold uppercase tracking-wide text-subtle">{{ $group }}</p>
                <ul class="mt-1.5 space-y-0.5">
                    @foreach ($visible as [$route, $label, $icon, $perm, $active])
                        @php $on = request()->routeIs(...explode('|', $active)); @endphp
                        <li><a href="{{ route($route) }}" @if ($on) aria-current="page" @endif
                            class="flex items-center gap-2.5 rounded-md px-2 py-1.5 text-sm {{ $on ? 'bg-elevated-2 text-text' : 'text-muted hover:bg-elevated hover:text-text' }}"><x-icon :name="$icon" class="size-4" />{{ $label }}</a></li>
                    @endforeach
                </ul>
            </div>
        @endif
    @endforeach
</nav>
