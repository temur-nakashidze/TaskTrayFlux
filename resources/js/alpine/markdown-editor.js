// Tiptap is loaded on demand, so pages without an editor don't download it.
export default (model, initial = "") => {
    let editor; // closure variable, not reactive Alpine state
    let dirty = false;
    let destroyed = false;

    // Unsaved-changes guards: full page leave and wire:navigate
    const onUnload = (e) => {
        if (dirty) {
            e.preventDefault();
            e.returnValue = "";
        }
    };
    const onNavigate = (e) => {
        if (
            dirty &&
            !confirm("You have unsaved changes. Leave without saving?")
        ) {
            e.preventDefault();
        }
    };

    return {
        tick: 0,

        async init() {
            window.addEventListener("beforeunload", onUnload);
            document.addEventListener("livewire:navigate", onNavigate);
            // dispatch this event from your Livewire save method
            this.$wire.$on("blueprint-saved", () => (dirty = false));

            try {
                const [
                    { Editor },
                    { default: StarterKit },
                    { Markdown },
                    { Placeholder },
                ] = await Promise.all([
                    import("@tiptap/core"),
                    import("@tiptap/starter-kit"),
                    import("@tiptap/markdown"),
                    import("@tiptap/extensions"),
                ]);

                if (destroyed) return; // component was removed while loading

                editor = new Editor({
                    element: this.$refs.editor,
                    extensions: [
                        StarterKit,
                        Markdown,
                        Placeholder.configure({
                            placeholder:
                                "Write your blueprint… try # for a heading or - for a list",
                        }),
                    ],
                    content: initial,
                    contentType: "markdown",
                    editorProps: {
                        attributes: {
                            class: "prose prose-zinc dark:prose-invert max-w-none min-h-64 focus:outline-none [overflow-wrap:anywhere]",
                        },
                    },
                    onUpdate: ({ editor }) => {
                        dirty = true; // only fires on real edits, not the initial load
                        this.$wire.$set(model, editor.getMarkdown(), false);
                    },
                    onTransaction: () => this.tick++,
                });
            } catch (error) {
                console.error("[markdownEditor] failed to start", error);
            }
        },

        run(command, attrs) {
            editor
                ?.chain()
                .focus()
                [command](...(attrs ? [attrs] : []))
                .run();
        },

        isActive(name, attrs) {
            void this.tick; // subscribe to editor transactions
            return editor?.isActive(name, ...(attrs ? [attrs] : [])) ?? false;
        },

        destroy() {
            destroyed = true;
            window.removeEventListener("beforeunload", onUnload);
            document.removeEventListener("livewire:navigate", onNavigate);
            editor?.destroy();
        },
    };
};
