import "./task-router.js";
const html = document.documentElement;
const body = document.body;

/* ---------- Helpers ---------- */

const nowSeconds = () => Math.floor(Date.now() / 1000);
const pad = (n) => String(n).padStart(2, "0");

const formatTime = (s) => {
    const h = Math.floor(s / 3600);
    const m = Math.floor((s % 3600) / 60);
    const sec = s % 60;
    return (h > 0 ? [h, m, sec] : [m, sec]).map(pad).join(":");
};

/* ---------- Page transitions ---------- */
// Transitions are off while a page is swapped in, and on after it has painted.
// `livewire:navigated` also fires on the initial load.

document.addEventListener("livewire:navigating", () =>
    html.classList.remove("transitions-ready"),
);

document.addEventListener("livewire:navigated", () =>
    requestAnimationFrame(() =>
        requestAnimationFrame(() => html.classList.add("transitions-ready")),
    ),
);

/* ---------- Drag flag ---------- */
// Lets sidebar branches auto-expand only while a *task* is dragged.
// `drop` is needed besides `dragend`: the dropped card gets removed from the DOM,
// and a removed source node may never deliver `dragend`.

let draggedCard = null;

document.addEventListener("dragstart", (e) => {
    const card = e.target.closest?.("[data-task-card]");
    if (!card) return;
    draggedCard = card;
    document.body.dataset.dragging = "task";
    document.body.dataset.taskId = card.getAttribute("wire:sort:item");
});

const clearDragging = () => {
    delete document.body.dataset.dragging;
    delete document.body.dataset.taskId;
};
document.addEventListener("dragend", clearDragging);
document.addEventListener("drop", clearDragging);

/* ---------- Alpine components ---------- */

document.addEventListener("alpine:init", () => {
    Alpine.data("taskTimer", (elapsed, startedAt, serverNow) => ({
        startedAt,
        offset: serverNow - nowSeconds(), // corrects client clock skew
        now: nowSeconds(),
        interval: null,

        init() {
            if (!this.startedAt) return;
            this.interval = setInterval(() => (this.now = nowSeconds()), 1000);
        },

        destroy() {
            clearInterval(this.interval);
        },

        get seconds() {
            if (!this.startedAt) return elapsed;
            return (
                elapsed + Math.max(0, this.now + this.offset - this.startedAt)
            );
        },

        get label() {
            return formatTime(this.seconds);
        },
    }));

    Alpine.data("panBoard", () => ({
        panning: false,
        startX: 0,
        startY: 0,
        left: 0,
        top: 0,

        down(e) {
            // only the empty background starts a pan
            if (e.button !== 0 || e.target !== this.$el) return;
            this.panning = true;
            this.startX = e.clientX;
            this.startY = e.clientY;
            this.left = this.$el.scrollLeft;
            this.top = this.$el.scrollTop;
        },

        move(e) {
            if (!this.panning) return;
            this.$el.scrollLeft = this.left - (e.clientX - this.startX);
            this.$el.scrollTop = this.top - (e.clientY - this.startY);
        },

        up() {
            this.panning = false;
        },

        insideVerticalScroller(el) {
            for (; el && el !== this.$el; el = el.parentElement) {
                const scrollable =
                    el.scrollHeight > el.clientHeight &&
                    ["auto", "scroll"].includes(getComputedStyle(el).overflowY);
                if (scrollable) return true;
            }
            return false;
        },
    }));

    // Drop-only zone: accepts dragged task cards, then removes the leftover DOM node.
    Alpine.data("trayDrop", (trayId) => ({
        over: false,

        enter(e) {
            if (document.body.dataset.dragging !== "task") return;
            e.preventDefault();
            this.over = true;
        },

        leave(e) {
            if (!this.$el.contains(e.relatedTarget)) this.over = false;
        },

        drop(e) {
            const id = document.body.dataset.taskId;
            const card = draggedCard;
            this.over = false;
            if (!id) return;
            e.preventDefault();

            // hide at once, the board re-render removes it for real
            if (card) card.style.display = "none";

            this.$wire.moveTask(+id, 0, trayId).catch(() => {
                // the move failed: bring the card back
                if (card) card.style.display = "";
            });
        },
    }));
});
Alpine.data("taskTimer", (elapsed, startedAt, serverNow, title = null) => ({
    startedAt,
    offset: serverNow - nowSeconds(),
    now: nowSeconds(),
    interval: null,
    pageTitle: null,
    onNavigated: null,

    init() {
        if (!this.startedAt) return;

        this.interval = setInterval(() => (this.now = nowSeconds()), 1000);

        if (title) {
            this.pageTitle = document.title;
            // wire:navigate swaps the page title, so remember the new one
            this.onNavigated = () => (this.pageTitle = document.title);
            document.addEventListener("livewire:navigated", this.onNavigated);

            document.title = `${this.label} · ${title}`;
            this.$watch("label", (l) => (document.title = `${l} · ${title}`));
        }
    },

    destroy() {
        clearInterval(this.interval);

        if (this.onNavigated) {
            document.removeEventListener(
                "livewire:navigated",
                this.onNavigated,
            );
            document.title = this.pageTitle;
        }
    },

    // seconds and label getters stay exactly as they are
}));
Alpine.data("newProject", (templates) => ({
    adding: false,
    templates,
    template: Alpine.$persist("default").as("new-project-template"),

    init() {
        // a stored template that no longer exists in the config
        if (!this.templates.some((t) => t.key === this.template)) {
            this.template = this.templates[0]?.key;
        }
    },

    open() {
        this.adding = true;
        this.$nextTick(() => this.$refs.newProject.focus());
    },

    cycle(step) {
        const i = this.templates.findIndex((t) => t.key === this.template);
        const n = this.templates.length;
        this.template = this.templates[(i + step + n) % n].key;
    },
}));
