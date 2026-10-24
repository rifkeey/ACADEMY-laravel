<x-layouts.admin :title="__('Badges')" :description="__('Each programme has one badge, awarded with the certificate. Retiring a badge stops new awards; badges already earned are kept.')">
    <div class="grid gap-6 lg:grid-cols-2">
        @foreach ($badges as $b)
            <x-ui.card class="p-5">
                <div class="flex items-center gap-4">
                    <x-credential-badge :name="$b->name" :hue="$b->hue" :icon="$b->icon" :size="72" />
                    <div><h2 class="font-semibold">{{ $b->name }}</h2><p class="text-sm text-muted">{{ $b->specialization->title }} · {{ __(':count awarded', ['count' => $awarded[$b->id] ?? 0]) }}</p></div>
                </div>
                <form method="POST" action="{{ route('admin.badges.update', $b) }}" class="mt-5 grid gap-4 sm:grid-cols-2">
                    @csrf @method('PUT')
                    <div class="sm:col-span-2"><x-ui.field :label="__('Name')" for="n{{ $b->id }}"><x-ui.input name="name" id="n{{ $b->id }}" :value="$b->name" required /></x-ui.field></div>
                    <div class="sm:col-span-2"><x-ui.field :label="__('Description')" for="d{{ $b->id }}"><x-ui.textarea name="description" id="d{{ $b->id }}" :value="$b->description" rows="2" required /></x-ui.field></div>
                    <div class="sm:col-span-2"><x-ui.field :label="__('Criteria')" for="c{{ $b->id }}"><x-ui.textarea name="criteria" id="c{{ $b->id }}" :value="$b->criteria" rows="2" required /></x-ui.field></div>
                    <x-ui.field :label="__('Colour hue (0–359)')" for="h{{ $b->id }}"><x-ui.input type="number" name="hue" id="h{{ $b->id }}" :value="$b->hue" min="0" max="359" required /></x-ui.field>
                    <x-ui.field :label="__('Icon')" for="i{{ $b->id }}"><x-ui.select name="icon" id="i{{ $b->id }}">@foreach (['radar', 'crosshair', 'scale', 'network', 'cloud', 'shield-check'] as $icon)<option value="{{ $icon }}" @selected($b->icon === $icon)>{{ $icon }}</option>@endforeach</x-ui.select></x-ui.field>
                    <x-ui.field :label="__('Status')" for="s{{ $b->id }}"><x-ui.select name="status" id="s{{ $b->id }}"><option value="active" @selected($b->status === 'active')>{{ __('Active') }}</option><option value="retired" @selected($b->status === 'retired')>{{ __('Retired') }}</option></x-ui.select></x-ui.field>
                    <div class="flex items-end"><x-ui.button size="sm">{{ __('Save badge') }}</x-ui.button></div>
                </form>
            </x-ui.card>
        @endforeach
    </div>
</x-layouts.admin>
