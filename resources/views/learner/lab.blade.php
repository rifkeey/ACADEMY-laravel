{{--
    Lab workspace. Everything shown here comes from the lab's learner-visible `content`; the
    answer keys (`validator`) never leave the server. Tasks are rendered by Blade and bound to the
    labRunner Alpine component, which saves a draft and posts answers for server-side grading.
--}}
<x-layouts.site :title="__('Lab: :title', ['title' => $lab->title])" :noindex="true">
    @php
        $c = $lab->content;
        $ws = $c['workspace'];
        $publicTasks = array_map(fn ($t) => ['id' => $t['id'], 'input' => $t['input'], 'hints' => $t['hints'] ?? []], $c['tasks']);
        // Workspace tabs: KQL console first (if any), then datasets/tables, then documents.
        $tabs = [];
        if ($ws['kind'] === 'kql') {
            $tabs[] = ['type' => 'console', 'label' => __('KQL console')];
            foreach ($ws['tables'] as $i => $t) $tabs[] = ['type' => 'dataset', 'label' => $t['name'], 'data' => $t, 'id' => "ds-{$i}"];
        }
        foreach ($ws['datasets'] ?? [] as $i => $d) $tabs[] = ['type' => 'dataset', 'label' => $d['name'], 'data' => $d, 'id' => "ds-{$i}"];
        foreach ($ws['documents'] ?? [] as $d) $tabs[] = ['type' => 'document', 'label' => $d['title'], 'data' => $d];
        $difficulty = ['foundation' => __('Foundation'), 'intermediate' => __('Intermediate'), 'advanced' => __('Advanced')];
        $field = 'w-full rounded-lg border border-border-strong bg-bg px-3 py-2 text-sm text-text placeholder:text-subtle focus:border-accent focus:outline-none';
    @endphp

    {{-- Public lab definition for the runner (no answer keys). --}}
    <script type="application/json" id="lab-content">@json(['tasks' => $publicTasks])</script>
    @foreach ($tabs as $tab)
        @if ($tab['type'] === 'dataset')
            <script type="application/json" id="{{ $tab['id'] }}">@json(['columns' => $tab['data']['columns'], 'rows' => $tab['data']['rows']])</script>
        @endif
    @endforeach

    <x-ui.container class="py-8">
        <nav aria-label="{{ __('Breadcrumb') }}" class="mb-4 text-sm text-muted">
            <a href="{{ route('learn.course', $slug) }}" class="hover:text-text">{{ $spec->title }}</a> <span aria-hidden="true">/</span> <span class="text-text">{{ __('Lab') }}</span>
        </nav>

        <x-site.english-notice :item="$lab" kind="lab" class="mb-6" />
        <header class="flex flex-col gap-4 border-b border-border pb-6 lg:flex-row lg:items-end lg:justify-between">
            <div class="max-w-3xl">
                <p class="text-sm font-semibold uppercase tracking-[0.14em] text-accent">{{ __('Practical lab') }}</p>
                <h1 class="mt-2 text-3xl font-semibold tracking-tight sm:text-4xl">{{ $lab->title }}</h1>
                <p class="mt-3 text-muted">{{ $lab->summary }}</p>
            </div>
            <div class="flex flex-wrap gap-2">
                <x-ui.pill>{{ $difficulty[$lab->difficulty] ?? ucfirst($lab->difficulty) }}</x-ui.pill>
                <x-ui.pill><x-icon name="clock" class="size-3" /> {{ __(':n min', ['n' => $lab->estimated_minutes]) }}</x-ui.pill>
                <x-ui.pill tone="accent">{{ __('Pass mark :pass%', ['pass' => $lab->pass_percent]) }}</x-ui.pill>
                @if ($completed)<x-ui.pill tone="success"><x-icon name="check-circle-2" class="size-3" /> {{ __('Passed') }}</x-ui.pill>@endif
            </div>
        </header>

        <div x-data="labRunner" data-lab-id="{{ $lab->id }}" data-submit-url="{{ route('learn.lab.submit', [$slug, $lab->slug]) }}" x-on:use-query="useQuery" class="mt-8 space-y-8">
            {{-- Briefing --}}
            <section aria-labelledby="brief-h" class="grid gap-6 lg:grid-cols-2">
                <x-ui.card class="p-6">
                    <h2 id="brief-h" class="font-semibold">{{ __('Scenario') }}</h2>
                    <x-markdown class="mt-3" :compact="true" :source="$c['scenario']" />
                    @if (! empty($c['objectives']))
                        <h3 class="mt-5 text-sm font-semibold">{{ __('Objectives') }}</h3>
                        <ul class="mt-2 list-disc space-y-1 pl-5 text-sm text-muted">@foreach ($c['objectives'] as $o)<li>{{ $o }}</li>@endforeach</ul>
                    @endif
                </x-ui.card>
                <x-ui.card class="space-y-4 p-6">
                    <div><h2 class="font-semibold">{{ __('Instructions') }}</h2><x-markdown class="mt-3" :compact="true" :source="$c['instructions']" /></div>
                    @if (! empty($c['scope']))<div><h3 class="text-sm font-semibold">{{ __('Scope') }}</h3><p class="mt-1 text-sm text-muted">{{ $c['scope'] }}</p></div>@endif
                    @if (! empty($c['simulationNotice']))
                        <x-ui.alert tone="info" :title="__('Simulated environment')">{{ $c['simulationNotice'] }}</x-ui.alert>
                    @endif
                </x-ui.card>
            </section>

            {{-- Workspace --}}
            <section aria-labelledby="ws-h" x-data="workspaceTabs">
                <h2 id="ws-h" class="text-lg font-semibold">{{ __('Workspace') }}</h2>
                <div role="tablist" aria-label="{{ __('Workspace') }}" class="mt-3 flex gap-1 overflow-x-auto overflow-y-hidden border-b border-border">
                    @foreach ($tabs as $i => $tab)
                        <button type="button" role="tab" id="tab-{{ $i }}" aria-controls="panel-{{ $i }}" data-index="{{ $i }}" @click="select"
                            :aria-selected="isActive({{ $i }})" :class="isActive({{ $i }}) ? 'border-accent text-text' : 'border-transparent text-muted hover:text-text'"
                            class="-mb-px inline-flex shrink-0 items-center gap-1.5 whitespace-nowrap border-b-2 px-3 py-2 text-sm font-medium">
                            <x-icon :name="$tab['type'] === 'console' ? 'search' : ($tab['type'] === 'dataset' ? 'layers' : 'file-text')" class="size-3.5" />{{ $tab['label'] }}
                        </button>
                    @endforeach
                </div>

                @foreach ($tabs as $i => $tab)
                    <div role="tabpanel" id="panel-{{ $i }}" aria-labelledby="tab-{{ $i }}" x-show="isActive({{ $i }})" @if ($i > 0) x-cloak @endif class="pt-4">
                        @if ($tab['type'] === 'console')
                            {{-- KQL console: queries run on the server against this lab's tables only. --}}
                            <div x-data="kqlConsole" data-endpoint="{{ route('learn.lab.query', [$slug, $lab->slug]) }}" data-sample="{{ $ws['sampleQuery'] ?? '' }}" class="space-y-3">
                                <label for="kql-query" class="text-sm font-medium">{{ __('Query') }} <span class="font-normal text-subtle">{{ __('(Ctrl+Enter to run)') }}</span></label>
                                <textarea id="kql-query" x-model="query" @keydown="keydown" rows="6" spellcheck="false" class="{{ $field }} font-mono"></textarea>
                                <div class="flex flex-wrap items-center gap-2">
                                    <x-ui.button type="button" size="sm" @click="run" x-bind:disabled="running"><x-icon name="play" class="size-3.5" /> {{ __('Run query') }}</x-ui.button>
                                    @if (collect($c['tasks'])->contains(fn ($t) => $t['input']['type'] === 'kql'))
                                        <x-ui.button type="button" size="sm" variant="secondary" @click="useInTask">{{ __('Use in selected task') }}</x-ui.button>
                                    @endif
                                    <span x-show="running" x-cloak class="text-sm text-muted">{{ __('Running…') }}</span>
                                </div>
                                <p x-show="error" x-cloak role="alert" class="rounded-lg border border-danger/40 bg-danger/10 px-3 py-2 font-mono text-sm text-danger" x-text="error"></p>
                                <div x-show="ran && ! error" x-cloak>
                                    <p class="mb-2 text-xs text-muted">{!! __(':count row(s)', ['count' => '<span x-text="rows.length"></span>']) !!}<span x-show="truncated"> {{ __('(truncated)') }}</span></p>
                                    <div class="max-h-[28rem] overflow-auto rounded-xl border border-border">
                                        <table class="w-full text-left font-mono text-xs">
                                            <thead class="sticky top-0 bg-elevated text-muted"><tr><template x-for="c in columns"><th scope="col" class="whitespace-nowrap px-3 py-2 font-semibold" x-text="c"></th></template></tr></thead>
                                            <tbody class="divide-y divide-border"><template x-for="row in rows"><tr><template x-for="c in columns"><td class="whitespace-nowrap px-3 py-1.5" x-text="cell(row, c)"></td></template></tr></template></tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        @elseif ($tab['type'] === 'dataset')
                            {{-- Evidence explorer: search, filter by column value, sort and page through rows. --}}
                            <div x-data="evidenceExplorer" data-source="{{ $tab['id'] }}" class="space-y-3">
                                @if (! empty($tab['data']['description']))<p class="text-sm text-muted">{{ $tab['data']['description'] }}</p>@endif
                                <div class="grid gap-2 sm:grid-cols-4">
                                    <div class="sm:col-span-2"><label for="q-{{ $i }}" class="sr-only">{{ __('Search rows') }}</label><input id="q-{{ $i }}" type="search" x-model="q" @input="resetPage" placeholder="{{ __('Search all columns') }}" class="{{ $field }}"></div>
                                    <div><label for="col-{{ $i }}" class="sr-only">{{ __('Filter column') }}</label>
                                        <select id="col-{{ $i }}" x-model="col" @change="changeColumn" class="{{ $field }}"><option value="">{{ __('Filter by column…') }}</option>@foreach ($tab['data']['columns'] as $col)<option value="{{ $col }}">{{ $col }}</option>@endforeach</select></div>
                                    <div><label for="val-{{ $i }}" class="sr-only">{{ __('Filter value') }}</label>
                                        <select id="val-{{ $i }}" x-model="val" @change="resetPage" x-bind:disabled="! col" class="{{ $field }}"><option value="">{{ __('Any value') }}</option><template x-for="v in values"><option x-bind:value="v" x-text="v"></option></template></select></div>
                                </div>
                                <div class="flex flex-wrap items-center justify-between gap-2 text-xs text-muted">
                                    <p>{!! __(':shown of :total rows', ['shown' => '<span x-text="filtered.length"></span>', 'total' => '<span x-text="rows.length"></span>']) !!}</p>
                                    <div class="flex items-center gap-2">
                                        <label for="sort-{{ $i }}">{{ __('Sort') }}</label>
                                        <select id="sort-{{ $i }}" x-model="sortKey" @change="resetPage" class="rounded-md border border-border-strong bg-bg px-2 py-1 text-xs">
                                            <option value="">{{ __('Original order') }}</option>
                                            @foreach ($tab['data']['columns'] as $col)<option value="{{ $col }}:1">{{ $col }} ↑</option><option value="{{ $col }}:-1">{{ $col }} ↓</option>@endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="max-h-[32rem] overflow-auto rounded-xl border border-border">
                                    <table class="w-full text-left font-mono text-xs">
                                        <caption class="sr-only">{{ $tab['label'] }}</caption>
                                        <thead class="sticky top-0 bg-elevated text-muted"><tr>@foreach ($tab['data']['columns'] as $col)<th scope="col" class="whitespace-nowrap px-3 py-2 font-semibold">{{ $col }}</th>@endforeach</tr></thead>
                                        <tbody class="divide-y divide-border"><template x-for="row in visible"><tr class="hover:bg-elevated/60"><template x-for="c in columns"><td class="whitespace-nowrap px-3 py-1.5" x-text="cell(row, c)"></td></template></tr></template></tbody>
                                    </table>
                                </div>
                                <div x-show="pages > 1" class="flex items-center justify-end gap-2 text-xs">
                                    <x-ui.button type="button" variant="ghost" size="sm" @click="prev"><x-icon name="arrow-left" class="size-3.5" /> {{ __('Previous') }}</x-ui.button>
                                    <span class="text-muted">{!! __('Page :page of :pages', ['page' => '<span x-text="page + 1"></span>', 'pages' => '<span x-text="pages"></span>']) !!}</span>
                                    <x-ui.button type="button" variant="ghost" size="sm" @click="next">{{ __('Next') }} <x-icon name="arrow-right" class="size-3.5" /></x-ui.button>
                                </div>
                            </div>
                        @else
                            @php $doc = $tab['data']; @endphp
                            <x-ui.card class="p-6">
                                <h3 class="mb-3 font-mono text-sm text-muted">{{ $doc['title'] }}</h3>
                                @if ($doc['format'] === 'code')
                                    <pre class="overflow-x-auto rounded-lg bg-bg p-4 font-mono text-xs leading-relaxed"><code>{{ $doc['body'] }}</code></pre>
                                @else
                                    <x-markdown :compact="true" :source="$doc['body']" />
                                @endif
                            </x-ui.card>
                        @endif
                    </div>
                @endforeach
            </section>

            {{-- Tasks --}}
            <section aria-labelledby="tasks-h">
                <h2 id="tasks-h" class="text-lg font-semibold">{{ __('Tasks') }}</h2>
                <ol class="mt-4 space-y-4">
                    @foreach ($c['tasks'] as $n => $task)
                        @php $id = $task['id']; $in = $task['input']; $key = \Illuminate\Support\Js::from($id); $fid = 'task-'.$id; @endphp
                        <li :class="taskClass({{ $key }})" class="rounded-2xl border bg-surface p-5 transition-colors">
                            <div class="flex items-start justify-between gap-4">
                                <div class="min-w-0">
                                    <p class="text-xs font-semibold uppercase tracking-wide text-subtle">{{ __('Task :n', ['n' => $n + 1]) }} · {{ trans_choice(':count point|:count points', $task['points']) }}</p>
                                    <x-markdown class="mt-1" :compact="true" :source="$task['prompt']" id="{{ $fid }}-prompt" />
                                </div>
                                <span x-show="isCorrect({{ $key }})" x-cloak class="shrink-0"><x-icon name="check-circle-2" class="size-5 text-success" :label="__('Correct')" /></span>
                                <span x-show="isWrong({{ $key }})" x-cloak class="shrink-0"><x-icon name="x-circle" class="size-5 text-danger" :label="__('Incorrect')" /></span>
                            </div>

                            <div class="mt-4">
                                @switch($in['type'])
                                    @case('text')
                                        <label for="{{ $fid }}" class="sr-only">{{ __('Answer for task :n', ['n' => $n + 1]) }}</label>
                                        <input id="{{ $fid }}" type="text" maxlength="500" autocomplete="off" x-model="answers[{{ $key }}]" placeholder="{{ $in['placeholder'] ?? 'Your answer' }}" class="{{ $field }} max-w-md">
                                        @break
                                    @case('number')
                                        <label for="{{ $fid }}" class="sr-only">{{ __('Answer for task :n', ['n' => $n + 1]) }}</label>
                                        <div class="flex max-w-xs items-center gap-2">
                                            <input id="{{ $fid }}" type="text" inputmode="decimal" maxlength="30" autocomplete="off" x-model="answers[{{ $key }}]" placeholder="{{ $in['placeholder'] ?? 'Number' }}" class="{{ $field }}">
                                            @if (! empty($in['unit']))<span class="text-sm text-muted">{{ $in['unit'] }}</span>@endif
                                        </div>
                                        @break
                                    @case('choice')
                                        <fieldset aria-labelledby="{{ $fid }}-prompt" class="min-w-0 space-y-2">
                                            @foreach ($in['options'] as $o)
                                                <label class="flex cursor-pointer items-start gap-3 rounded-lg border border-border px-3 py-2.5 text-sm hover:bg-elevated">
                                                    <input type="radio" name="{{ $fid }}" value="{{ $o['id'] }}" x-model="answers[{{ $key }}]" class="mt-0.5 accent-[var(--accent)]"><span>{{ $o['label'] }}</span>
                                                </label>
                                            @endforeach
                                        </fieldset>
                                        @break
                                    @case('multi')
                                        <fieldset aria-labelledby="{{ $fid }}-prompt" class="min-w-0 space-y-2">
                                            <p class="text-xs text-subtle">{{ __('Select all that apply.') }}</p>
                                            @foreach ($in['options'] as $o)
                                                <label class="flex cursor-pointer items-start gap-3 rounded-lg border border-border px-3 py-2.5 text-sm hover:bg-elevated">
                                                    <input type="checkbox" value="{{ $o['id'] }}" x-model="answers[{{ $key }}]" class="mt-0.5 accent-[var(--accent)]"><span>{{ $o['label'] }}</span>
                                                </label>
                                            @endforeach
                                        </fieldset>
                                        @break
                                    @case('kql')
                                        <div class="space-y-2">
                                            <label for="{{ $fid }}" class="text-sm font-medium">{{ isset($in['table']) ? __('Your query (table :table)', ['table' => $in['table']]) : __('Your query') }}</label>
                                            <textarea id="{{ $fid }}" rows="4" maxlength="4000" spellcheck="false" x-model="answers[{{ $key }}]" @focus="selectKqlTask({{ $key }})" class="{{ $field }} font-mono"></textarea>
                                            <button type="button" @click="selectKqlTask({{ $key }})" class="text-xs text-muted underline hover:text-text">
                                                <span x-show="! isSelectedKql({{ $key }})">{{ __('Send queries from the console to this task') }}</span>
                                                <span x-show="isSelectedKql({{ $key }})" x-cloak class="text-accent">{{ __('Console queries go to this task') }}</span>
                                            </button>
                                        </div>
                                        @break
                                    @case('grid')
                                        <div class="overflow-x-auto rounded-xl border border-border">
                                            <table class="w-full text-left text-sm">
                                                <thead class="bg-elevated text-xs uppercase tracking-wide text-muted"><tr><th scope="col" class="px-3 py-2">{{ __('Item') }}</th>@foreach ($in['columns'] as $col)<th scope="col" class="px-3 py-2">{{ $col['label'] }}</th>@endforeach</tr></thead>
                                                <tbody class="divide-y divide-border">
                                                    @foreach ($in['rows'] as $row)
                                                        <tr>
                                                            <th scope="row" class="min-w-56 px-3 py-2 font-normal">{{ $row['label'] }}</th>
                                                            @foreach ($in['columns'] as $col)
                                                                <td class="px-3 py-2">
                                                                    <label for="{{ $fid }}-{{ $row['id'] }}-{{ $col['id'] }}" class="sr-only">{{ __(':column for :item', ['column' => $col['label'], 'item' => $row['label']]) }}</label>
                                                                    <select id="{{ $fid }}-{{ $row['id'] }}-{{ $col['id'] }}" x-model="answers[{{ $key }}][{{ \Illuminate\Support\Js::from($row['id']) }}][{{ \Illuminate\Support\Js::from($col['id']) }}]" class="min-w-28 rounded-md border border-border-strong bg-bg px-2 py-1.5 text-sm">
                                                                        <option value="">—</option>
                                                                        @foreach ($col['options'] as $o)<option value="{{ $o['id'] }}">{{ $o['label'] }}</option>@endforeach
                                                                    </select>
                                                                </td>
                                                            @endforeach
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                        @break
                                    @case('iam-policy')
                                        {{-- Policy editor: graded by a server-side evaluator against hidden access requests. --}}
                                        <div class="space-y-2">
                                            <label for="{{ $fid }}" class="text-sm font-medium">{{ __('Your policy (JSON)') }}</label>
                                            <textarea id="{{ $fid }}" rows="18" maxlength="20000" spellcheck="false" x-model="answers[{{ $key }}]" class="{{ $field }} font-mono text-xs leading-relaxed"></textarea>
                                            <p class="text-xs text-subtle">{{ __('Evaluation: an explicit Deny wins; otherwise an Allow is needed; anything not allowed is denied. Wildcards * and ? are supported. Conditions are not evaluated in this simulator.') }}</p>
                                        </div>
                                        @break
                                    @case('deliverable')
                                        {{-- Written deliverable: kept in your portfolio. The automatic check covers completeness only. --}}
                                        <div class="space-y-3">
                                            <label for="{{ $fid }}" class="text-sm font-medium">{{ __('Your deliverable') }}</label>
                                            <textarea id="{{ $fid }}" rows="14" maxlength="20000" x-model="answers[{{ $key }}]" class="{{ $field }} leading-relaxed"></textarea>
                                            <p class="text-xs text-subtle">{{ __('At least :words words. Saved to your portfolio when you submit. The automatic check confirms the required points are covered; use the rubric to judge the quality of your reasoning.', ['words' => $in['minWords'] ?? 0]) }}</p>
                                            @if (! empty($in['rubric']))
                                                <details class="rounded-lg border border-border bg-bg/40 p-3 text-sm">
                                                    <summary class="cursor-pointer font-medium text-accent-strong">{{ __('Marking rubric') }}</summary>
                                                    <ul class="mt-2 list-disc space-y-1 pl-5 text-muted">@foreach ($in['rubric'] as $r)<li>{{ $r }}</li>@endforeach</ul>
                                                </details>
                                            @endif
                                        </div>
                                        @break
                                    @case('firewall')
                                        {{-- Ordered rule builder: first matching rule wins, anything unmatched is denied. --}}
                                        <div class="space-y-3">
                                            <p class="text-xs text-subtle">{{ __('Rules are evaluated top to bottom; the first match wins and unmatched traffic is denied. Up to :max rules. Port accepts a number or "any".', ['max' => $in['maxRules']]) }}</p>
                                            <template x-for="(rule, index) in answers[{{ $key }}]">
                                                <div class="grid grid-cols-2 gap-2 rounded-lg border border-border p-3 sm:grid-cols-[2rem_repeat(5,minmax(0,1fr))_auto] sm:items-center">
                                                    <span class="col-span-2 text-xs font-semibold text-subtle sm:col-span-1" x-text="index + 1"></span>
                                                    <select x-model="rule.action" aria-label="{{ __('Action') }}" class="rounded-md border border-border-strong bg-bg px-2 py-1.5 text-sm"><option value="allow">{{ __('Allow') }}</option><option value="deny">{{ __('Deny') }}</option></select>
                                                    <select x-model="rule.source" aria-label="{{ __('Source zone') }}" class="rounded-md border border-border-strong bg-bg px-2 py-1.5 text-sm"><option value="any">{{ __('any source') }}</option>@foreach ($in['zones'] as $z)<option value="{{ $z }}">{{ __('from :zone', ['zone' => $z]) }}</option>@endforeach</select>
                                                    <select x-model="rule.destination" aria-label="{{ __('Destination zone') }}" class="rounded-md border border-border-strong bg-bg px-2 py-1.5 text-sm"><option value="any">{{ __('any destination') }}</option>@foreach ($in['zones'] as $z)<option value="{{ $z }}">{{ __('to :zone', ['zone' => $z]) }}</option>@endforeach</select>
                                                    <select x-model="rule.protocol" aria-label="{{ __('Protocol') }}" class="rounded-md border border-border-strong bg-bg px-2 py-1.5 text-sm"><option value="tcp">{{ __('TCP') }}</option><option value="udp">{{ __('UDP') }}</option><option value="any">{{ __('any protocol') }}</option></select>
                                                    <input x-model="rule.port" aria-label="{{ __('Port') }}" maxlength="5" class="rounded-md border border-border-strong bg-bg px-2 py-1.5 text-sm">
                                                    <div class="col-span-2 flex justify-end gap-1 sm:col-span-1">
                                                        <button type="button" @click="moveRule({{ $key }}, index, -1)" class="rounded p-1.5 text-muted hover:bg-elevated hover:text-text" aria-label="{{ __('Move rule up') }}"><x-icon name="chevron-down" class="size-4 rotate-180" /></button>
                                                        <button type="button" @click="moveRule({{ $key }}, index, 1)" class="rounded p-1.5 text-muted hover:bg-elevated hover:text-text" aria-label="{{ __('Move rule down') }}"><x-icon name="chevron-down" class="size-4" /></button>
                                                        <button type="button" @click="removeRule({{ $key }}, index)" class="rounded p-1.5 text-danger hover:bg-danger/10" aria-label="{{ __('Remove rule') }}"><x-icon name="trash-2" class="size-4" /></button>
                                                    </div>
                                                </div>
                                            </template>
                                            <x-ui.button type="button" variant="secondary" size="sm" @click="addRule({{ $key }})" x-bind:disabled="! canAddRule({{ $key }})"><x-icon name="plus" class="size-3.5" /> {{ __('Add rule') }}</x-ui.button>
                                        </div>
                                        @break
                                @endswitch
                            </div>

                            {{-- Hints are revealed one at a time and counted in the attempt record. --}}
                            @if (! empty($task['hints']))
                                <div class="mt-4 space-y-2">
                                    @foreach ($task['hints'] as $h => $hint)
                                        <p x-show="hintShown({{ $key }}, {{ $h }})" x-cloak class="flex gap-2 rounded-lg border border-warning/30 bg-warning/5 px-3 py-2 text-sm text-muted"><x-icon name="lightbulb" class="mt-0.5 size-4 shrink-0 text-warning" />{{ $hint }}</p>
                                    @endforeach
                                    <button type="button" x-show="hintsLeft({{ $key }}) > 0" @click="showHint({{ $key }})" class="inline-flex items-center gap-1 text-xs text-muted underline hover:text-text"><x-icon name="lightbulb" class="size-3.5" /> {{ __('Show a hint') }}</button>
                                </div>
                            @endif

                            <p x-show="resultFor({{ $key }})" x-cloak role="status" :class="isCorrect({{ $key }}) ? 'text-success' : 'text-danger'" class="mt-4 text-sm" x-text="feedbackFor({{ $key }})"></p>
                        </li>
                    @endforeach
                </ol>
            </section>

            {{-- Submission and result --}}
            <section aria-labelledby="submit-h" class="rounded-2xl border border-border bg-surface p-6">
                <h2 id="submit-h" class="font-semibold">{{ __('Submit for checking') }}</h2>
                <p class="mt-1 text-sm text-muted">{{ __('Answers are checked on the server. You can resubmit as often as you need; your first passing attempt counts towards completion. Your draft is saved in this browser.') }}</p>
                <div class="mt-4 flex flex-wrap items-center gap-3">
                    <x-ui.button type="button" @click="submit" x-bind:disabled="pending"><x-icon name="file-check-2" class="size-4" /> {{ __('Submit answers') }}</x-ui.button>
                    <span x-show="pending" x-cloak class="text-sm text-muted">{{ __('Checking…') }}</span>
                </div>
                <p x-show="error" x-cloak role="alert" class="mt-4 text-sm text-danger" x-text="error"></p>

                <div id="lab-result" tabindex="-1" x-show="result" x-cloak class="mt-6 rounded-xl border p-5 focus:outline-none" :class="result && result.passed ? 'border-success/40 bg-success/10' : 'border-warning/40 bg-warning/10'">
                    <p class="text-lg font-semibold"><span x-text="result && result.passed ? {{ Js::from(__('Lab passed')) }} : {{ Js::from(__('Not passed yet')) }}"></span> · <span x-text="scorePercent"></span>%</p>
                    <p class="mt-1 text-sm text-muted">{!! __(':score of :max points. Pass mark :pass%.', ['score' => '<span x-text="result ? result.score : 0"></span>', 'max' => '<span x-text="result ? result.maxScore : 0"></span>', 'pass' => $lab->pass_percent]) !!}</p>
                    <p x-show="result && result.passed" class="mt-3 text-sm"><a href="{{ route('learn.course', $slug) }}" class="font-semibold text-text underline">{{ __('Back to the course') }}</a></p>
                </div>

                {{-- Worked solution and professional debrief: sent by the server only after a pass. --}}
                <div x-show="result && result.debriefHtml" x-cloak class="mt-6 rounded-xl border border-border bg-bg/40 p-5">
                    <h3 class="font-semibold">{{ __('Debrief and worked solution') }}</h3>
                    <div class="prose-amaniq prose-compact mt-3" x-html="result ? result.debriefHtml : ''"></div>
                </div>
            </section>

            @if (! empty($c['reflection']))
                <section aria-labelledby="refl-h" class="rounded-2xl border border-border bg-surface p-6">
                    <h2 id="refl-h" class="font-semibold">{{ __('Reflect and apply') }}</h2>
                    <p class="mt-1 text-sm text-muted">{{ __('Questions a senior colleague would ask you about this work. Note your answers; they make good interview preparation.') }}</p>
                    <ol class="mt-3 list-decimal space-y-1.5 pl-5 text-sm text-muted">@foreach ($c['reflection'] as $q)<li>{{ $q }}</li>@endforeach</ol>
                </section>
            @endif

            @if ($attempts->isNotEmpty())
                <section aria-labelledby="hist-h">
                    <h2 id="hist-h" class="font-semibold">{{ __('Recent attempts') }}</h2>
                    <div class="mt-3">
                        <x-ui.table :head="[__('Submitted'), __('Score'), __('Hints'), __('Result')]" :caption="__('Your recent attempts at this lab')">
                            @foreach ($attempts as $a)
                                <tr>
                                    <x-ui.td>{{ $a->submitted_at->translatedFormat('j M Y, H:i') }} UTC</x-ui.td>
                                    <x-ui.td class="tabular-nums">{{ $a->score }}/{{ $a->max_score }}</x-ui.td>
                                    <x-ui.td class="tabular-nums">{{ $a->hints_used }}</x-ui.td>
                                    <x-ui.td>@if ($a->passed)<x-ui.pill tone="success">{{ __('Passed') }}</x-ui.pill>@else<x-ui.pill>{{ __('Not passed') }}</x-ui.pill>@endif</x-ui.td>
                                </tr>
                            @endforeach
                        </x-ui.table>
                    </div>
                </section>
            @endif
        </div>
    </x-ui.container>
</x-layouts.site>
