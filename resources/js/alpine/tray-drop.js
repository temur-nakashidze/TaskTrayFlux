import { getDraggedCard } from "../drag-state.js";

// Drop-only zone: accepts dragged task cards, then removes the leftover DOM node.
export default (trayId) => ({
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
        const card = getDraggedCard();
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
});
