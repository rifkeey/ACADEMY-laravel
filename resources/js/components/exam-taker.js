/*
 * Examination taker. The page is a normal HTML form (radios and checkboxes named
 * responses[position][]), so it submits correctly even without JavaScript. This component adds
 * one-question-at-a-time navigation, flags, an answered counter, a draft in sessionStorage, and
 * automatic submission when the server-side deadline passes.
 */
export function examTaker() {
    return {
        current: 0,
        total: 0,
        answered: {},
        flags: {},
        confirming: false,
        submitting: false,
        draftKey: '',

        init() {
            this.total = Number(this.$el.dataset.total);
            this.draftKey = `amaniq-exam-${this.$el.dataset.attempt}`;
            this.restore();
            this.track();
        },

        inputs() {
            return [...this.$refs.form.querySelectorAll('input[name^="responses["]')];
        },

        /** Recomputes which questions have an answer and saves the draft. */
        track() {
            const answered = {};
            const draft = [];
            for (const input of this.inputs()) {
                if (input.checked) {
                    answered[input.dataset.position] = true;
                    draft.push(input.id);
                }
            }
            this.answered = answered;
            try {
                sessionStorage.setItem(this.draftKey, JSON.stringify(draft));
            } catch {
                /* ignore */
            }
        },

        restore() {
            try {
                const ids = JSON.parse(sessionStorage.getItem(this.draftKey) || '[]');
                for (const id of ids) {
                    const el = document.getElementById(id);
                    if (el) el.checked = true;
                }
            } catch {
                /* ignore */
            }
        },

        get answeredCount() {
            return Object.keys(this.answered).length;
        },
        get unanswered() {
            return this.total - this.answeredCount;
        },
        isCurrent(index) {
            return this.current === index;
        },
        go(event) {
            this.current = Number(event.currentTarget.dataset.index);
            this.confirming = false;
        },
        prev() {
            if (this.current > 0) this.current--;
        },
        next() {
            if (this.current < this.total - 1) this.current++;
        },
        get isFirst() {
            return this.current === 0;
        },
        get isLast() {
            return this.current === this.total - 1;
        },
        toggleFlag() {
            this.flags[this.current] = !this.flags[this.current];
        },
        get isFlagged() {
            return !!this.flags[this.current];
        },
        /** Classes for a question number in the navigator: current, flagged, answered or untouched. */
        navClass(index) {
            if (this.current === index) return 'border-accent bg-accent text-accent-ink';
            if (this.flags[index]) return 'border-warning/60 bg-warning/15 text-text';
            if (this.answered[String(index + 1)]) return 'border-border-strong bg-elevated-2 text-text';
            return 'border-border text-muted hover:bg-elevated';
        },
        isFlaggedAt(index) {
            return !!this.flags[index];
        },
        review() {
            this.confirming = true;
        },
        keepWorking() {
            this.confirming = false;
        },
        submit() {
            if (this.submitting) return;
            this.submitting = true;
            try {
                sessionStorage.removeItem(this.draftKey);
            } catch {
                /* ignore */
            }
            this.$refs.form.submit();
        },
        /** Called by the countdown when time runs out. The server applies a short grace period. */
        expired() {
            this.submit();
        },
    };
}
