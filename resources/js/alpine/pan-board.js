export default () => ({
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
});
