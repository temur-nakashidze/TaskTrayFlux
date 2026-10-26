import { nowSeconds, formatTime } from "../lib/time.js";

export default (elapsed, startedAt, serverNow, title = null) => ({
    startedAt,
    offset: serverNow - nowSeconds(), // corrects client clock skew
    now: nowSeconds(),
    interval: null,
    pageTitle: null,
    lastTitle: null,
    onNavigated: null,

    init() {
        if (!this.startedAt) return;

        this.interval = setInterval(() => (this.now = nowSeconds()), 1000);

        if (title) {
            this.pageTitle = document.title;
            // wire:navigate swaps the page title, so remember the new one
            this.onNavigated = () => (this.pageTitle = document.title);
            document.addEventListener("livewire:navigated", this.onNavigated);

            this.setTitle();
            this.$watch("label", () => this.setTitle());
        }
    },

    destroy() {
        clearInterval(this.interval);

        if (this.onNavigated) {
            document.removeEventListener(
                "livewire:navigated",
                this.onNavigated,
            );
            // only restore if nothing else (e.g. a navigation) changed the title
            if (document.title === this.lastTitle) {
                document.title = this.pageTitle;
            }
        }
    },

    setTitle() {
        this.lastTitle = `${this.label} · ${title}`;
        document.title = this.lastTitle;
    },

    get seconds() {
        if (!this.startedAt) return elapsed;
        return elapsed + Math.max(0, this.now + this.offset - this.startedAt);
    },

    get label() {
        return formatTime(this.seconds);
    },
});
