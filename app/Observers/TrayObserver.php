<?php

namespace App\Observers;

use App\Models\Tray;

class TrayObserver
{
    /**
     * A Tray's children differ depending on what it belongs to: real Tasks
     * for a Project/Shelf tray, or WorkbenchTask references for a Workbench
     * tray. Running both is harmless - one of the two will always be empty.
     */
    public function deleting(Tray $tray): void
    {
        $tray->tasks()->get()->each(fn ($task) => $task->delete());
    }

    public function restoring(Tray $tray): void
    {
        $tray->tasks()->onlyTrashed()->get()->each(fn ($task) => $task->restore());
    }

    public function forceDeleting(Tray $tray): void
    {
        $tray->tasks()->withTrashed()->get()->each(fn ($task) => $task->forceDelete());
    }
}
