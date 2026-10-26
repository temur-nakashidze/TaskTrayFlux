const slug = (s) => s.toLowerCase().trim().replace(/\s+/g, "-");

const fuzzy = (q, s) => {
    q = q.toLowerCase();
    let i = 0;
    for (const ch of s.toLowerCase()) if (ch === q[i]) i++;
    return i === q.length;
};

// exact slug first, then fuzzy; blank text never matches
const findNamed = (list, text) => {
    const t = text.toLowerCase();
    if (!t) return null;
    return (
        list.find((x) => slug(x.name) === t) ??
        list.find((x) => fuzzy(t, x.name)) ??
        null
    );
};

// second level: the project's shelves and its own trays
const dests = (p) => [
    ...(p.shelves ?? []).map((s) => ({
        kind: "shelf",
        tint: p.tint,
        id: s.id,
        name: s.name,
    })),
    ...p.trays.map((t) => ({ kind: "tray", tint: p.tint, ...t })),
];

export default (projects = [], tags = [], mode = "all") => {
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

        // ---- route mode (@project/shelf/tray) ----
        get segs() {
            return this.query.split("/");
        },

        // 0 = typing the project, 1 = shelf or tray, 2 = tray of a shelf
        get depth() {
            return this.segs.length - 1;
        },

        get project() {
            return this.depth >= 1
                ? findNamed(this.projects, this.segs[0])
                : null;
        },

        get shelf() {
            return this.depth >= 2 && this.project
                ? findNamed(this.project.shelves ?? [], this.segs[1])
                : null;
        },

        get routeLabel() {
            if (!this.route) return "";
            const { project, shelf, dest } = this.route;
            return (
                "→ " +
                [project.name, shelf?.name, dest?.name]
                    .filter(Boolean)
                    .join(" / ")
            );
        },

        // ---- tag mode (#) ----
        get tagItems() {
            const q = this.query.toLowerCase();
            const taken = new Set(this.chosen.map((t) => t.name.toLowerCase()));
            const list = this.tags
                .filter(
                    (t) => !taken.has(t.name.toLowerCase()) && fuzzy(q, t.name),
                )
                .map((t) => ({ kind: "tag", ...t }));
            const exists =
                taken.has(q) ||
                this.tags.some((t) => t.name.toLowerCase() === q);
            if (q && !exists)
                list.push({
                    kind: "new-tag",
                    id: "new",
                    name: this.query,
                    tint: "text-zinc-400",
                });
            return list;
        },

        removeTag(i) {
            this.chosen.splice(i, 1);
        },

        // ---- popup list for the active mode ----
        get items() {
            if (this.current === "tag") return this.tagItems;

            const [a, b = "", c = ""] = this.segs;

            if (this.depth === 0) {
                return this.projects
                    .filter((x) => fuzzy(a, x.name))
                    .map((x) => ({ kind: "project", ...x }));
            }
            if (!this.project) return [];

            if (this.depth === 1) {
                return dests(this.project).filter((x) => fuzzy(b, x.name));
            }
            if (this.depth === 2 && this.shelf) {
                return this.shelf.trays
                    .map((t) => ({
                        kind: "tray",
                        tint: this.project.tint,
                        ...t,
                    }))
                    .filter((x) => fuzzy(c, x.name));
            }
            return [];
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

            // projects and shelves drill one level deeper: the popup stays open
            if (item.kind === "project") {
                this.replaceToken("@" + slug(item.name) + "/");
                return;
            }
            if (item.kind === "shelf") {
                this.replaceToken(
                    "@" + slug(this.project.name) + "/" + slug(item.name) + "/",
                );
                return;
            }

            // a tray ends the path
            this.route = {
                project: this.project,
                shelf: this.shelf,
                dest: item,
            };
            this.replaceToken("");
            this.open = false;
        },

        // Esc: keep what is resolved so far (project, or project and shelf)
        acceptCurrent() {
            if (!this.project) {
                this.open = false;
                return;
            }
            this.route = {
                project: this.project,
                shelf: this.shelf,
                dest: null,
            };
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
            } else if (this.current === "route" && this.depth > 0)
                this.acceptCurrent();
            else this.open = false;
        },

        submit() {
            const el = this.el;
            const title = el.value.replace(/ {2,}/g, " ").trim();
            if (!title) return;

            this.$dispatch("task-submit", {
                title,
                projectId: this.route?.project.id ?? null,
                shelfId: this.route?.shelf?.id ?? null,
                trayId: this.route?.dest?.id ?? null,
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
};
