import taskTimer from "./task-timer.js";
import panBoard from "./pan-board.js";
import trayDrop from "./tray-drop.js";
import newProject from "./new-project.js";
import taskRouter from "./task-router.js";
import markdownEditor from "./markdown-editor.js";

document.addEventListener("alpine:init", () => {
    Alpine.data("taskTimer", taskTimer);
    Alpine.data("panBoard", panBoard);
    Alpine.data("trayDrop", trayDrop);
    Alpine.data("newProject", newProject);
    Alpine.data("taskRouter", taskRouter);
    Alpine.data("markdownEditor", markdownEditor);
});
