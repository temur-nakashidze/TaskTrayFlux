// Lets sidebar branches auto-expand only while a *task* is dragged.
// `drop` is needed besides `dragend`: the dropped card gets removed from the DOM,
// and a removed source node may never deliver `dragend`.
let draggedCard = null;

export const getDraggedCard = () => draggedCard;

document.addEventListener("dragstart", (e) => {
    const card = e.target.closest?.("[data-task-card]");
    if (!card) return;
    draggedCard = card;
    document.body.dataset.dragging = "task";
    document.body.dataset.taskId = card.getAttribute("wire:sort:item");
});

const clearDragging = () => {
    draggedCard = null;
    delete document.body.dataset.dragging;
    delete document.body.dataset.taskId;
};

document.addEventListener("dragend", clearDragging);
document.addEventListener("drop", clearDragging);
