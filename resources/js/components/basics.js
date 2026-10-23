/** Opens/closes a native <dialog> used to confirm destructive actions. */
export function confirmDialog() {
    return {
        open() {
            this.$refs.dialog.showModal();
        },
        close() {
            this.$refs.dialog.close();
        },
    };
}

/**
 * Formative self-check in a lesson. Not graded or recorded, so the answer index is in the page
 * by design; graded assessment happens only in labs and the server-side exam.
 */
export function knowledgeCheck() {
    return {
        // Single-answer checks lock on the first choice; multi-select checks collect ticks and
        // are marked when the learner presses "Check answer".
        multi: false,
        answers: [],
        selected: [],
        answered: false,
        init() {
            this.multi = this.$el.dataset.multi === '1';
            this.answers = this.$el.dataset.answer.split(',').map(Number);
        },
        choose() {
            const i = Number(this.$el.dataset.index);
            if (this.answered) return;
            if (!this.multi) {
                this.selected = [i];
                this.answered = true;
                return;
            }
            this.selected = this.selected.includes(i) ? this.selected.filter((x) => x !== i) : [...this.selected, i];
        },
        check() {
            if (this.selected.length) this.answered = true;
        },
        reset() {
            this.selected = [];
            this.answered = false;
            for (const input of this.$root.querySelectorAll('input')) input.checked = false;
        },
        get canCheck() {
            return this.multi && !this.answered && this.selected.length > 0;
        },
        get isCorrect() {
            return this.selected.length === this.answers.length && this.answers.every((a) => this.selected.includes(a));
        },
        optionClass() {
            const i = Number(this.$el.dataset.index);
            if (!this.answered) return this.selected.includes(i) ? 'border-accent/60 bg-accent/5' : 'border-border hover:bg-elevated';
            if (this.answers.includes(i)) return 'border-success/60 bg-success/10';
            if (this.selected.includes(i)) return 'border-danger/60 bg-danger/10';
            return 'border-border';
        },
        /** Per-option feedback is shown for the options the learner picked, once answered. */
        showFeedback() {
            return this.answered && this.selected.includes(Number(this.$el.dataset.index));
        },
    };
}

/** mm:ss countdown to data-expires-at (ISO). Emits "expired" once when it reaches zero. */
export function countdown() {
    return {
        left: 0,
        timer: null,
        fired: false,
        init() {
            const end = Date.parse(this.$el.dataset.expiresAt);
            const tick = () => {
                this.left = Math.max(0, end - Date.now());
                if (this.left === 0 && !this.fired) {
                    this.fired = true;
                    this.$dispatch('expired');
                }
            };
            tick();
            this.timer = setInterval(tick, 1000);
        },
        destroy() {
            clearInterval(this.timer);
        },
        get text() {
            const m = Math.floor(this.left / 60000);
            const s = Math.floor((this.left % 60000) / 1000);
            return `${String(m).padStart(2, '0')}:${String(s).padStart(2, '0')}`;
        },
        get urgent() {
            return this.left < 5 * 60000;
        },
    };
}

/** Opens the browser print dialog (print or save the page as PDF). */
export function printPage() {
    return {
        print() {
            window.print();
        },
    };
}
