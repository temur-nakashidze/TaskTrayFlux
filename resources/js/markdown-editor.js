import { Editor } from "@tiptap/core";
import StarterKit from "@tiptap/starter-kit";
import { Markdown } from "@tiptap/markdown";
import { Placeholder } from "@tiptap/extensions";

function markdownEditor(model, initial = "") {
    let editor; // closure variable, not reactive Alpine state
    let dirty = false;

    const getMarkdown = (ed) =>
        typeof ed.getMarkdown === "function"
            ? ed.getMarkdown()
            : ed.storage.markdown.getMarkdown();

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

        init() {
            try {
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
                        dirty = true; // onUpdate only fires on real edits, not the initial load
                        this.$wire.$set(model, getMarkdown(editor), false);
                    },
                    onTransaction: () => this.tick++,
                });

                window.addEventListener("beforeunload", onUnload);
                window.addEventListener("livewire:navigate", onNavigate);
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
            this.tick;
            return editor?.isActive(name, ...(attrs ? [attrs] : [])) ?? false;
        },

        destroy() {
            window.removeEventListener("beforeunload", onUnload);
            window.removeEventListener("livewire:navigate", onNavigate);
            editor?.destroy();
        },
    };
}

window.markdownEditor = markdownEditor;
