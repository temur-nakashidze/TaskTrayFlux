const slug = (s) => s.toLowerCase().trim().replace(/\s+/g, "-");

const fuzzy = (q, s) => {
    q = q.toLowerCase();
    let i = 0;
    for (const ch of s.toLowerCase()) if (ch === q[i]) i++;
    return i === q.length;
};

const dests = (p) => [
    ...p.trays.map((t) => ({ kind: "tray", tint: p.tint, ...t })),
    // ...p.shelves.map((s) => ({ kind: 'shelf', ...s })), // later
];

document.addEventListener("alpine:init", () => {
    Alpine.data("taskRouter", (projects = [], tags = [], mode = "all") => {
        // which triggers are active: 'all' | 'route' | 'tag'
        const enabled = ["route", "tag"].includes(mode) ? mode : "all";
        const chars = [
            ["route", "@"],
            ["tag", "#"],
        ]
            .filter(([m]) => enabled === "all" || enabled === m)
            .map(([, c]) => c)
            .join("");
        const pattern = new RegExp(`(^|\\s)([${chars}])([^\\s@#]*)$`);

        return {
            projects,
            tags,
            open: false,
            query: "",
            current: "route",
            active: 0,
            route: null,
            chosen: [],
            start: 0,
            end: 0,

            // the router works with whatever textarea sits inside the wrapper
            get el() {
                return this.$root.querySelector("textarea");
            },

            // ---- route mode (@) ----
            get parts() {
                const i = this.query.indexOf("/");
                return i < 0
                    ? { p: this.query, d: "", deep: false }
                    : {
                          p: this.query.slice(0, i),
                          d: this.query.slice(i + 1),
                          deep: true,
                      };
            },

            findProject(p) {
                p = p.toLowerCase();
                return (
                    this.projects.find((x) => slug(x.name) === p) ??
                    this.projects.find((x) => fuzzy(p, x.name)) ??
                    null
                );
            },

            get project() {
                return this.parts.deep ? this.findProject(this.parts.p) : null;
            },

            get routeLabel() {
                if (!this.route) return "";
                return (
                    "→ " +
                    this.route.project.name +
                    (this.route.dest ? " / " + this.route.dest.name : "")
                );
            },

            // ---- tag mode (#) ----
            get tagItems() {
                const q = this.query.toLowerCase();
                const taken = new Set(
                    this.chosen.map((t) => t.name.toLowerCase()),
                );
                const list = this.tags
                    .filter(
                        (t) =>
                            !taken.has(t.name.toLowerCase()) &&
                            fuzzy(q, t.name),
                    )
                    .map((t) => ({ kind: "tag", ...t }));
                const exists =
                    taken.has(q) ||
                    this.tags.some((t) => t.name.toLowerCase() === q);
                if (q && !exists)
                    if (q && !exists)
                        list.push({
                            kind: "new-tag",
                            id: "new",
                            name: this.query,
                            tint: "bg-zinc-400",
                        });
                return list;
            },

            removeTag(i) {
                this.chosen.splice(i, 1);
            },

            // ---- popup list for the active mode ----
            get items() {
                if (this.current === "tag") return this.tagItems;

                const { p, d, deep } = this.parts;
                if (!deep) {
                    return this.projects
                        .filter((x) => fuzzy(p, x.name))
                        .map((x) => ({ kind: "project", ...x }));
                }
                return this.project
                    ? dests(this.project).filter((x) => fuzzy(d, x.name))
                    : [];
            },

            // ---- text handling ----
            detect(e) {
                const el = this.el;
                if (!el || (e && e.target !== el)) return;

                // real typing only (synthetic events have no inputType): emptying the box clears everything
                if (e?.inputType && !el.value.trim()) {
                    this.route = null;
                    this.chosen = [];
                }

                const pos = el.selectionStart;
                const m = el.value.slice(0, pos).match(pattern);
                this.open = !!m;
                if (m) {
                    this.current = m[2] === "@" ? "route" : "tag";
                    this.query = m[3];
                    this.start = pos - m[3].length - 1;
                    this.end = pos;
                    this.active = 0;
                }
            },

            replaceToken(text) {
                const el = this.el;
                el.setRangeText(text, this.start, this.end, "end");
                let caret = el.selectionStart;
                const cleaned = el.value.replace(/ {2,}/g, " ");
                if (cleaned !== el.value) el.value = cleaned;
                caret = Math.min(caret, el.value.length);
                el.setSelectionRange(caret, caret);
                el.dispatchEvent(new Event("input", { bubbles: true })); // keeps resize / models in sync
                el.focus();
            },

            pick(item) {
                if (item.kind === "tag" || item.kind === "new-tag") {
                    this.chosen.push({
                        id: item.kind === "tag" ? item.id : null,
                        name: item.name,
                        isNew: item.kind === "new-tag",
                        chip:
                            item.chip ??
                            "bg-zinc-400/20 text-zinc-700 dark:bg-zinc-400/40 dark:text-zinc-200",
                    });
                    this.replaceToken("");
                    this.open = false;
                    return;
                }

                if (item.kind === "project") {
                    this.replaceToken("@" + slug(item.name) + "/"); // input event re-runs detect: popup moves to the destination level
                    return;
                }

                this.route = { project: this.project, dest: item };
                this.replaceToken("");
                this.open = false;
            },

            acceptProjectOnly() {
                if (!this.project) {
                    this.open = false;
                    return;
                }
                this.route = { project: this.project, dest: null };
                this.replaceToken("");
                this.open = false;
            },

            // capture phase on the wrapper: runs before the textarea's own Enter handler
            key(e) {
                if (e.target !== this.el || e.isComposing) return;

                if (!this.open) {
                    if (e.key === "Enter" && !e.shiftKey) {
                        e.preventDefault();
                        e.stopPropagation();
                        this.submit();
                    }
                    return;
                }

                const n = this.items.length;
                const handled = [
                    "ArrowDown",
                    "ArrowUp",
                    "Enter",
                    "Tab",
                    "Escape",
                ].includes(e.key);
                if (!handled) return;
                e.preventDefault();
                e.stopPropagation();

                if (e.key === "ArrowDown")
                    this.active = (this.active + 1) % Math.max(n, 1);
                else if (e.key === "ArrowUp")
                    this.active =
                        (this.active - 1 + Math.max(n, 1)) % Math.max(n, 1);
                else if (e.key === "Enter" || e.key === "Tab") {
                    if (n) this.pick(this.items[this.active]);
                    else {
                        this.open = false;
                        if (e.key === "Enter") this.submit();
                    }
                } else if (this.current === "route" && this.parts.deep)
                    this.acceptProjectOnly();
                else this.open = false;
            },

            submit() {
                const el = this.el;
                const title = el.value.replace(/ {2,}/g, " ").trim();
                if (!title) return;

                this.$dispatch("task-submit", {
                    title,
                    projectId: this.route?.project.id ?? null,
                    kind: this.route?.dest?.kind ?? null,
                    destId: this.route?.dest?.id ?? null,
                    tags: this.chosen.map(({ id, name }) => ({ id, name })),
                });

                // names created just now should appear in the popup without a reload
                this.chosen
                    .filter((t) => t.isNew)
                    .forEach((t) => this.tags.push({ id: null, name: t.name }));

                el.value = "";
                el.dispatchEvent(new Event("input", { bubbles: true }));
                this.route = null;
                this.chosen = [];
                this.open = false;
            },
        };
    });
});
