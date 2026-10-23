/*
 * Lab runner (Alpine CSP components).
 *
 * The lab's public definition is embedded in the page as JSON (no answer keys). Answers are kept
 * in `answers`, saved as a draft in this browser, and sent to the server for grading. The KQL
 * console and the grading both run on the server, so there is a single implementation of each.
 */

const csrf = () => document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ?? '';

async function postJson(url, body) {
    const res = await fetch(url, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', Accept: 'application/json', 'X-CSRF-TOKEN': csrf(), 'X-Requested-With': 'XMLHttpRequest' },
        credentials: 'same-origin',
        body: JSON.stringify(body),
    });
    let data = null;
    try {
        data = await res.json();
    } catch {
        /* non-JSON error page */
    }
    return { status: res.status, data };
}

function readJson(id) {
    const el = document.getElementById(id);
    return el ? JSON.parse(el.textContent) : null;
}

export function labRunner() {
    return {
        tasks: [],
        answers: {},
        hints: {},
        result: null,
        error: null,
        pending: false,
        selectedKqlTask: null,
        draftKey: '',
        submitUrl: '',

        init() {
            // Read data attributes now: inside event handlers `this.$el` is the clicked element.
            this.submitUrl = this.$el.dataset.submitUrl;
            const lab = readJson('lab-content');
            this.tasks = lab.tasks;
            this.draftKey = `amaniq-lab-${this.$el.dataset.labId}`;
            const fresh = {};
            for (const t of this.tasks) {
                // Shapes each input type expects, so x-model always has a place to write.
                if (t.input.type === 'multi' || t.input.type === 'firewall') fresh[t.id] = [];
                else if (t.input.type === 'grid') fresh[t.id] = Object.fromEntries(t.input.rows.map((r) => [r.id, Object.fromEntries(t.input.columns.map((c) => [c.id, '']))]));
                else if (t.input.type === 'iam-policy') fresh[t.id] = t.input.starter ?? '';
                else if (t.input.type === 'deliverable') fresh[t.id] = t.input.template ?? '';
                else fresh[t.id] = '';
                this.hints[t.id] = 0;
                if (t.input.type === 'kql' && !this.selectedKqlTask) this.selectedKqlTask = t.id;
            }
            // Restore a saved draft, ignoring any value whose shape no longer matches the task.
            const draft = this.loadDraft();
            for (const id of Object.keys(fresh)) {
                if (id in draft && Array.isArray(draft[id]) === Array.isArray(fresh[id]) && typeof draft[id] === typeof fresh[id]) fresh[id] = draft[id];
            }
            this.answers = fresh;
            this.$watch('answers', () => this.saveDraft());
        },

        loadDraft() {
            try {
                return JSON.parse(localStorage.getItem(this.draftKey) || '{}');
            } catch {
                return {};
            }
        },

        saveDraft() {
            try {
                localStorage.setItem(this.draftKey, JSON.stringify(this.answers));
            } catch {
                /* storage unavailable: keep the draft in memory only */
            }
        },

        // ---- hints -------------------------------------------------------
        showHint(taskId) {
            const task = this.tasks.find((t) => t.id === taskId);
            this.hints[taskId] = Math.min(task.hints.length, this.hints[taskId] + 1);
        },
        hintShown(taskId, index) {
            return index < this.hints[taskId];
        },
        hintsLeft(taskId) {
            const task = this.tasks.find((t) => t.id === taskId);
            return task.hints.length - this.hints[taskId];
        },
        get hintsUsed() {
            return Object.values(this.hints).reduce((a, b) => a + b, 0);
        },

        // ---- KQL console → task -----------------------------------------
        selectKqlTask(taskId) {
            this.selectedKqlTask = taskId;
        },
        isSelectedKql(taskId) {
            return this.selectedKqlTask === taskId;
        },
        useQuery(event) {
            if (this.selectedKqlTask) this.answers[this.selectedKqlTask] = event.detail;
        },

        // ---- firewall rule builder --------------------------------------
        addRule(taskId) {
            const task = this.tasks.find((t) => t.id === taskId);
            if (this.answers[taskId].length >= task.input.maxRules) return;
            this.answers[taskId].push({ action: 'allow', source: task.input.zones[0], destination: task.input.zones[1] ?? task.input.zones[0], protocol: 'tcp', port: 443 });
        },
        removeRule(taskId, index) {
            this.answers[taskId].splice(index, 1);
        },
        moveRule(taskId, index, delta) {
            const rules = this.answers[taskId];
            const j = index + delta;
            if (j < 0 || j >= rules.length) return;
            [rules[index], rules[j]] = [rules[j], rules[index]];
        },
        canAddRule(taskId) {
            const task = this.tasks.find((t) => t.id === taskId);
            return this.answers[taskId].length < task.input.maxRules;
        },

        // ---- submission and feedback ------------------------------------
        async submit() {
            this.pending = true;
            this.error = null;
            const { status, data } = await postJson(this.submitUrl, { answers: this.normalisedAnswers(), hints_used: this.hintsUsed });
            this.pending = false;
            if (status === 200 && data) {
                this.result = data;
                this.$nextTick(() => document.getElementById('lab-result')?.focus());
            } else if (status === 429) {
                this.error = 'You have submitted this lab many times recently. Take a break and try again later.';
            } else {
                this.error = data?.message ?? 'The submission could not be checked. Please try again.';
            }
        },

        /** Numbers and firewall ports are sent as numbers; everything else as typed. */
        normalisedAnswers() {
            const out = {};
            for (const t of this.tasks) {
                const v = this.answers[t.id];
                if (t.input.type === 'firewall') out[t.id] = v.map((r) => ({ ...r, port: r.port === 'any' || r.port === '' ? 'any' : Number(r.port) }));
                else out[t.id] = v;
            }
            return out;
        },

        resultFor(taskId) {
            return this.result?.results.find((r) => r.taskId === taskId) ?? null;
        },
        taskClass(taskId) {
            const r = this.resultFor(taskId);
            if (r) return r.correct ? 'border-success/50' : 'border-danger/50';
            return this.isSelectedKql(taskId) ? 'border-accent/60' : 'border-border';
        },
        isCorrect(taskId) {
            const r = this.resultFor(taskId);
            return !!r && r.correct;
        },
        isWrong(taskId) {
            const r = this.resultFor(taskId);
            return !!r && !r.correct;
        },
        feedbackFor(taskId) {
            const r = this.resultFor(taskId);
            if (!r) return '';
            return [r.feedback, r.detail].filter(Boolean).join(' ');
        },
        get scorePercent() {
            return this.result ? Math.floor((this.result.score * 100) / Math.max(1, this.result.maxScore)) : 0;
        },
    };
}

/** Searchable, filterable, sortable table explorer for supplied evidence (public synthetic data). */
export function evidenceExplorer() {
    return {
        columns: [],
        rows: [],
        q: '',
        col: '',
        val: '',
        sortKey: '',
        page: 0,
        perPage: 50,

        init() {
            const ds = readJson(this.$el.dataset.source);
            this.columns = ds.columns;
            this.rows = ds.rows;
        },
        get filtered() {
            const needle = this.q.trim().toLowerCase();
            let rows = this.rows.filter((r) => {
                if (this.col && this.val !== '' && String(r[this.col] ?? '') !== this.val) return false;
                if (!needle) return true;
                return this.columns.some((c) => String(r[c] ?? '').toLowerCase().includes(needle));
            });
            if (this.sortKey) {
                const [key, dir] = this.sortKey.split(':');
                const d = Number(dir);
                rows = [...rows].sort((a, b) => (String(a[key] ?? '') < String(b[key] ?? '') ? -d : String(a[key] ?? '') > String(b[key] ?? '') ? d : 0));
            }
            return rows;
        },
        get visible() {
            return this.filtered.slice(this.page * this.perPage, this.page * this.perPage + this.perPage);
        },
        get pages() {
            return Math.max(1, Math.ceil(this.filtered.length / this.perPage));
        },
        get values() {
            if (!this.col) return [];
            return [...new Set(this.rows.map((r) => String(r[this.col] ?? '')))].sort().slice(0, 200);
        },
        resetPage() {
            this.page = 0;
        },
        changeColumn() {
            this.val = '';
            this.page = 0;
        },
        prev() {
            if (this.page > 0) this.page--;
        },
        next() {
            if (this.page < this.pages - 1) this.page++;
        },
        cell(row, c) {
            const v = row[c];
            return v === null || v === undefined || v === '' ? '—' : String(v);
        },
    };
}

/** KQL console: sends the query to the server, which runs it over this lab's datasets only. */
export function kqlConsole() {
    return {
        query: '',
        columns: [],
        rows: [],
        truncated: false,
        error: null,
        running: false,
        ran: false,
        endpoint: '',

        init() {
            this.endpoint = this.$el.dataset.endpoint;
            this.query = this.$el.dataset.sample ?? '';
        },
        async run() {
            this.running = true;
            this.error = null;
            const { status, data } = await postJson(this.endpoint, { query: this.query });
            this.running = false;
            this.ran = true;
            if (status === 200 && data?.ok) {
                this.columns = data.columns;
                this.rows = data.rows;
                this.truncated = data.truncated;
            } else {
                this.columns = [];
                this.rows = [];
                this.error = status === 429 ? 'Too many queries in a short time. Wait a moment and try again.' : (data?.error ?? data?.message ?? 'The query could not be run.');
            }
        },
        keydown(event) {
            if ((event.ctrlKey || event.metaKey) && event.key === 'Enter') {
                event.preventDefault();
                this.run();
            }
        },
        useInTask() {
            this.$dispatch('use-query', this.query);
        },
        cell(row, c) {
            const v = row[c];
            return v === null || v === undefined || v === '' ? '—' : String(v);
        },
    };
}

/** Accessible tab list for the workspace (datasets, documents, console). */
export function workspaceTabs() {
    return {
        active: 0,
        select(event) {
            this.active = Number(event.currentTarget.dataset.index);
        },
        isActive(index) {
            return this.active === index;
        },
    };
}
