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
        picked: null,
        answer: 0,
        init() {
            this.answer = Number(this.$el.dataset.answer);
        },
        choose() {
            if (this.picked === null) this.picked = Number(this.$el.dataset.index);
        },
        reset() {
            this.picked = null;
        },
        get answered() {
            return this.picked !== null;
        },
        get isCorrect() {
            return this.picked === this.answer;
        },
        optionClass() {
            const i = Number(this.$el.dataset.index);
            if (this.picked === null) return 'border-border hover:bg-elevated';
            if (i === this.answer) return 'border-success/60 bg-success/10';
            if (i === this.picked) return 'border-danger/60 bg-danger/10';
            return 'border-border';
        },
        isPicked() {
            return this.picked === Number(this.$el.dataset.index);
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
