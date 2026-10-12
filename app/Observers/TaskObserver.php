<?php

namespace App\Observers;

use App\Models\Task;
use RuntimeException;

class TaskObserver
{
    /**
     * A Task cannot be marked complete while it has any incomplete Cut.
     * This is deliberately an app-layer rule, not a DB constraint.
     */
    public function saving(Task $task): void
    {
        if ($task->completed && $task->isDirty('completed_at') && ! $task->canBeCompleted()) {
            throw new RuntimeException(
                'This task has incomplete cuts and cannot be marked complete yet.'
            );
        }
    }

    public function deleting(Task $task): void
    {
        $task->cuts()->get()->each(fn ($cut) => $cut->delete());
        $task->timeEntries()->delete();
        $task->workbenchReference()?->delete();
    }

    public function restoring(Task $task): void
    {
        $task->cuts()->onlyTrashed()->get()->each(fn ($cut) => $cut->restore());
        $task->workbenchReference()->onlyTrashed()->get()->each(fn ($wt) => $wt->restore());
    }
}
