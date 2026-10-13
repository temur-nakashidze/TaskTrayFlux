<?php

namespace App\Observers;

use App\Models\Task;

class TaskObserver
{
    public function deleting(Task $task): void
    {
        $task->cuts()->get()->each(fn ($cut) => $cut->delete());
        $task->timeEntries()->delete();
        $task->workbenchReference()?->delete();
    }

    public function restoring(Task $task): void
    {
        $task->cuts()->onlyTrashed()->get()->each(fn ($cut) => $cut->restore());
    }
}
