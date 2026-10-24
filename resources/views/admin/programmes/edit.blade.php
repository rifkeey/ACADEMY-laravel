@php
    $p = $programme;
    $join = fn ($a) => \App\Support\Lines::join($a);
    $faqText = collect($p->faqs ?? [])->map(fn ($f) => "Q: {$f['q']}\nA: {$f['a']}")->implode("\n\n");
    $structuralOk = $selected && $selected->status === 'draft';
@endphp
<x-layouts.admin :title="$p->title" :description="__('Edit the public programme page, control publication and manage course versions.')">
    <div class="grid gap-8 xl:grid-cols-[1fr_22rem]">
        <div class="space-y-8">
            {{-- Course versions and structure --}}
            <section aria-labelledby="ver-h">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <h2 id="ver-h" class="text-lg font-semibold">{{ __('Course structure') }}</h2>
                    <div class="flex flex-wrap gap-2">
                        @foreach ($versions as $v)
                            <a href="{{ route('admin.programmes.edit', [$p, 'version' => $v->id]) }}" @if ($selected?->id === $v->id) aria-current="true" @endif
                               class="rounded-lg border px-3 py-1.5 text-sm {{ $selected?->id === $v->id ? 'border-accent bg-accent/10 text-text' : 'border-border text-muted hover:bg-elevated' }}">v{{ $v->version }} · {{ $v->status }}</a>
                        @endforeach
                    </div>
                </div>

                @if ($selected)
                    @if ($selected->status === 'published')
                        <x-ui.alert tone="info" class="mt-4" :title="__('This version is live')">{{ __('Wording can be corrected here. To add, remove, reorder or change what is required, create a draft version.') }}
                            @unless ($versions->contains('status', 'draft'))
                                <form method="POST" action="{{ route('admin.programmes.versions.store', $p) }}" class="mt-3">@csrf<x-ui.button size="sm">{{ __('Create draft version') }}</x-ui.button></form>
                            @endunless
                        </x-ui.alert>
                    @elseif ($selected->status === 'retired')
                        <x-ui.alert tone="warning" class="mt-4">{{ __('Retired versions are read-only. Learners who enrolled on it keep access.') }}</x-ui.alert>
                    @else
                        <x-ui.alert tone="warning" class="mt-4" title="Draft version {{ $selected->version }}">{{ __('Only staff can see this version. Publish it when it is ready; existing learners stay on their version.') }}</x-ui.alert>
                    @endif

                    <ol class="mt-5 space-y-3">
                        @foreach ($modules as $m)
                            <li class="rounded-2xl border border-border bg-surface">
                                <details>
                                    <summary class="flex cursor-pointer list-none flex-wrap items-center justify-between gap-3 p-4 [&::-webkit-details-marker]:hidden">
                                        <span class="flex items-center gap-3"><span class="text-xs font-semibold text-subtle">{{ $m->position }}</span><span class="font-medium">{{ $m->title }}</span></span>
                                        <span class="flex items-center gap-2">@include('admin.partials.status', ['value' => $m->status]) @include('admin.partials.status', ['value' => $m->content_status])<span class="text-xs text-muted">{{ $m->lessons->count() }} lessons · {{ $m->labs->count() }} labs</span></span>
                                    </summary>
                                    <div class="space-y-5 border-t border-border p-4">
                                        <ul class="space-y-1 text-sm">
                                            @foreach ($m->lessons as $l)
                                                <li class="flex items-center justify-between gap-3 rounded-md px-2 py-1.5 hover:bg-elevated">
                                                    <a href="{{ route('admin.lessons.edit', $l) }}" class="hover:text-accent">{{ $l->position }}. {{ $l->title }}</a>
                                                    <span class="flex gap-1.5">@if (! $l->required)<x-ui.pill>{{ __('Optional') }}</x-ui.pill>@endif @include('admin.partials.status', ['value' => $l->status])</span>
                                                </li>
                                            @endforeach
                                            @foreach ($m->labs as $l)
                                                <li class="flex items-center justify-between gap-3 rounded-md px-2 py-1.5 hover:bg-elevated">
                                                    <a href="{{ route('admin.labs.edit', $l) }}" class="inline-flex items-center gap-1.5 hover:text-accent"><x-icon name="flask-conical" class="size-3.5 text-accent" />Lab: {{ $l->title }}</a>
                                                    @include('admin.partials.status', ['value' => $l->status])
                                                </li>
                                            @endforeach
                                        </ul>

                                        @if ($selected->status !== 'retired')
                                            <form method="POST" action="{{ route('admin.modules.update', $m) }}" class="grid gap-3 sm:grid-cols-2">
                                                @csrf @method('PUT')
                                                <div class="sm:col-span-2"><x-ui.field :label="__('Module title')" for="m{{ $m->id }}-title"><x-ui.input name="title" id="m{{ $m->id }}-title" :value="$m->title" required /></x-ui.field></div>
                                                <x-ui.field :label="__('Purpose')" for="m{{ $m->id }}-purpose"><x-ui.textarea name="purpose" id="m{{ $m->id }}-purpose" :value="$m->purpose" rows="3" /></x-ui.field>
                                                <x-ui.field :label="__('Prerequisite knowledge')" for="m{{ $m->id }}-pre"><x-ui.textarea name="prerequisite_knowledge" id="m{{ $m->id }}-pre" :value="$m->prerequisite_knowledge" rows="3" /></x-ui.field>
                                                <x-ui.field :label="__('Objectives (one per line)')" for="m{{ $m->id }}-obj"><x-ui.textarea name="objectives" id="m{{ $m->id }}-obj" :value="$join($m->objectives)" rows="4" /></x-ui.field>
                                                <x-ui.field :label="__('Key concepts (one per line)')" for="m{{ $m->id }}-kc"><x-ui.textarea name="key_concepts" id="m{{ $m->id }}-kc" :value="$join($m->key_concepts)" rows="4" /></x-ui.field>
                                                <x-ui.field :label="__('Assessment criteria (one per line)')" for="m{{ $m->id }}-ac"><x-ui.textarea name="assessment_criteria" id="m{{ $m->id }}-ac" :value="$join($m->assessment_criteria)" rows="4" /></x-ui.field>
                                                <x-ui.field :label="__('Instructor notes (staff only)')" for="m{{ $m->id }}-notes"><x-ui.textarea name="instructor_notes" id="m{{ $m->id }}-notes" :value="$m->instructor_notes" rows="4" /></x-ui.field>
                                                <x-ui.field :label="__('Content status')" for="m{{ $m->id }}-cs"><x-ui.select name="content_status" id="m{{ $m->id }}-cs">@foreach (['complete', 'starter', 'outline'] as $s)<option value="{{ $s }}" @selected($m->content_status === $s)>{{ __(ucfirst($s)) }}</option>@endforeach</x-ui.select></x-ui.field>
                                                <x-ui.field :label="__('Next review date')" for="m{{ $m->id }}-rd"><x-ui.input type="date" name="review_date" id="m{{ $m->id }}-rd" :value="$m->review_date?->format('Y-m-d')" /></x-ui.field>
                                                <div class="sm:col-span-2"><x-ui.button size="sm">{{ __('Save module') }}</x-ui.button></div>
                                            </form>
                                        @endif

                                        @if ($structuralOk)
                                            <div class="flex flex-wrap items-end gap-3 border-t border-border pt-4">
                                                <form method="POST" action="{{ route('admin.modules.move', $m) }}">@csrf<input type="hidden" name="direction" value="up"><x-ui.button variant="ghost" size="sm">{{ __('Move up') }}</x-ui.button></form>
                                                <form method="POST" action="{{ route('admin.modules.move', $m) }}">@csrf<input type="hidden" name="direction" value="down"><x-ui.button variant="ghost" size="sm">{{ __('Move down') }}</x-ui.button></form>
                                                <form method="POST" action="{{ route('admin.modules.status', $m) }}" class="flex items-center gap-2">
                                                    @csrf @method('PUT')
                                                    <label for="m{{ $m->id }}-st" class="sr-only">{{ __('Module status') }}</label>
                                                    <select id="m{{ $m->id }}-st" name="status" class="h-8 rounded-md border border-border-strong bg-bg px-2 text-sm">@foreach (['draft', 'published', 'archived'] as $s)<option value="{{ $s }}" @selected($m->status === $s)>{{ __(ucfirst($s)) }}</option>@endforeach</select>
                                                    <x-ui.button variant="secondary" size="sm">{{ __('Set status') }}</x-ui.button>
                                                </form>
                                                <form method="POST" action="{{ route('admin.lessons.store', $m) }}" class="flex flex-wrap items-end gap-2">
                                                    @csrf
                                                    <div><label for="m{{ $m->id }}-lt" class="text-xs text-muted">{{ __('New lesson title') }}</label><input id="m{{ $m->id }}-lt" name="title" required maxlength="160" class="block h-8 rounded-md border border-border-strong bg-bg px-2 text-sm"></div>
                                                    <div><label for="m{{ $m->id }}-ls" class="text-xs text-muted">{{ __('Slug') }}</label><input id="m{{ $m->id }}-ls" name="slug" required maxlength="120" pattern="[a-z0-9]+(-[a-z0-9]+)*" class="block h-8 rounded-md border border-border-strong bg-bg px-2 font-mono text-sm"></div>
                                                    <x-ui.button size="sm"><x-icon name="plus" class="size-3.5" /> {{ __('Add lesson') }}</x-ui.button>
                                                </form>
                                            </div>
                                        @endif
                                    </div>
                                </details>
                            </li>
                        @endforeach
                    </ol>

                    @if ($structuralOk)
                        <form method="POST" action="{{ route('admin.modules.store', $selected) }}" class="mt-4 flex flex-wrap items-end gap-3 rounded-2xl border border-dashed border-border-strong p-4">
                            @csrf
                            <div class="w-64"><x-ui.field :label="__('New module title')" for="nm-title"><x-ui.input name="title" id="nm-title" required /></x-ui.field></div>
                            <div class="w-56"><x-ui.field :label="__('Slug')" for="nm-slug"><x-ui.input name="slug" id="nm-slug" required pattern="[a-z0-9]+(-[a-z0-9]+)*" class="font-mono" /></x-ui.field></div>
                            <x-ui.button variant="secondary"><x-icon name="plus" class="size-4" /> {{ __('Add module') }}</x-ui.button>
                        </form>
                    @endif
                @else
                    <x-ui.empty :title="__('No versions yet')" icon="layers" class="mt-4" />
                @endif
            </section>

            {{-- Public details --}}
            <section aria-labelledby="det-h">
                <h2 id="det-h" class="text-lg font-semibold">{{ __('Programme page') }}</h2>
                <form method="POST" action="{{ route('admin.programmes.update', $p) }}" class="mt-4 grid gap-4 rounded-2xl border border-border bg-surface p-5 sm:grid-cols-2">
                    @csrf @method('PUT')
                    <div class="sm:col-span-2"><x-ui.field :label="__('Title')" for="title"><x-ui.input name="title" :value="$p->title" required /></x-ui.field></div>
                    <x-ui.field :label="__('Category')" for="category"><x-ui.select name="category">@foreach ($categories as $k => $label)<option value="{{ $k }}" @selected(old('category', $p->category) === $k)>{{ $label }}</option>@endforeach</x-ui.select></x-ui.field>
                    <x-ui.field :label="__('Level')" for="level"><x-ui.input name="level" :value="$p->level" required /></x-ui.field>
                    <x-ui.field :label="__('Estimated study hours')" for="estimated_hours"><x-ui.input type="number" name="estimated_hours" :value="$p->estimated_hours" min="1" required /></x-ui.field>
                    <x-ui.field :label="__('Sort order')" for="sort_order"><x-ui.input type="number" name="sort_order" :value="$p->sort_order" min="0" required /></x-ui.field>
                    <div class="sm:col-span-2"><x-ui.field :label="__('Summary (shown on cards)')" for="summary"><x-ui.textarea name="summary" :value="$p->summary" rows="2" maxlength="400" required /></x-ui.field></div>
                    <div class="sm:col-span-2"><x-ui.field :label="__('Description (Markdown)')" for="description"><x-ui.textarea name="description" :value="$p->description" rows="6" required /></x-ui.field></div>
                    <x-ui.field :label="__('Who it is for (one per line)')" for="audience"><x-ui.textarea name="audience" :value="$join($p->audience)" rows="4" /></x-ui.field>
                    <x-ui.field :label="__('Prerequisites (one per line)')" for="prerequisites"><x-ui.textarea name="prerequisites" :value="$join($p->prerequisites)" rows="4" /></x-ui.field>
                    <x-ui.field :label="__('Outcomes (one per line)')" for="outcomes"><x-ui.textarea name="outcomes" :value="$join($p->outcomes)" rows="5" /></x-ui.field>
                    <x-ui.field :label="__('What is included (one per line)')" for="inclusions"><x-ui.textarea name="inclusions" :value="$join($p->inclusions)" rows="5" /></x-ui.field>
                    <div class="sm:col-span-2"><x-ui.field :label="__('FAQs')" for="faqs" :hint="__('Write each as a Q: line followed by an A: line, with a blank line between questions.')"><x-ui.textarea name="faqs" :value="$faqText" rows="8" /></x-ui.field></div>
                    <div class="sm:col-span-2"><x-ui.button>{{ __('Save programme page') }}</x-ui.button></div>
                </form>
            </section>
        </div>

        <aside class="space-y-6">
            @can('content.publish')
                <x-ui.card class="p-5">
                    <h2 class="font-semibold">{{ __('Publication') }}</h2>
                    <form method="POST" action="{{ route('admin.programmes.publication', $p) }}" class="mt-4 space-y-4">
                        @csrf @method('PUT')
                        <x-ui.field :label="__('Visibility')" for="status"><x-ui.select name="status">@foreach (['draft', 'published', 'archived'] as $s)<option value="{{ $s }}" @selected($p->status === $s)>{{ __(ucfirst($s)) }}</option>@endforeach</x-ui.select></x-ui.field>
                        <x-ui.field :label="__('Enrolment')" for="enrolment_open"><x-ui.select name="enrolment_open"><option value="1" @selected($p->enrolment_open)>{{ __('Open') }}</option><option value="0" @selected(! $p->enrolment_open)>{{ __('Closed') }}</option></x-ui.select></x-ui.field>
                        <x-ui.field :label="__('Content status')" for="content_status" :hint="__('Shown publicly so learners know whether all content is ready.')"><x-ui.select name="content_status"><option value="complete" @selected($p->content_status === 'complete')>{{ __('Complete') }}</option><option value="in_development" @selected($p->content_status === 'in_development')>{{ __('In development') }}</option></x-ui.select></x-ui.field>
                        <x-ui.button size="sm">{{ __('Save publication') }}</x-ui.button>
                    </form>
                </x-ui.card>

                @php $draft = $versions->firstWhere('status', 'draft'); @endphp
                @if ($draft)
                    <x-ui.card class="p-5">
                        <h2 class="font-semibold">{{ __('Publish draft v:n', ['n' => $draft->version]) }}</h2>
                        <p class="mt-1 text-sm text-muted">{{ __('New enrolments get this version. The current live version is retired; its learners keep access to it.') }}</p>
                        <form method="POST" action="{{ route('admin.versions.publish', $draft) }}" class="mt-4 space-y-3">
                            @csrf
                            <x-ui.field :label="__('What changed')" for="changelog"><x-ui.textarea name="changelog" rows="4" required minlength="5" /></x-ui.field>
                            <x-ui.button size="sm">{{ __('Publish version :n', ['n' => $draft->version]) }}</x-ui.button>
                        </form>
                    </x-ui.card>
                @endif
            @endcan

            <x-ui.card class="p-5 text-sm">
                <h2 class="font-semibold">{{ __('Version history') }}</h2>
                <ul class="mt-3 space-y-3">
                    @foreach ($versions as $v)
                        <li><p class="font-medium">v{{ $v->version }} · {{ __(ucfirst($v->status)) }}</p>@if ($v->published_at)<p class="text-xs text-subtle">{{ __('Published :date', ['date' => $v->published_at->translatedFormat('j M Y')]) }}</p>@endif @if ($v->changelog)<p class="mt-1 text-muted">{{ $v->changelog }}</p>@endif</li>
                    @endforeach
                </ul>
            </x-ui.card>
        </aside>
    </div>
</x-layouts.admin>
