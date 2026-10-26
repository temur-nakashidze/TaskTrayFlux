export default (templates) => ({
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
});
