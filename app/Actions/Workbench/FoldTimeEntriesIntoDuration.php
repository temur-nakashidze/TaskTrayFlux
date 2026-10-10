<?php

namespace App\Actions\Workbench;

use App\Models\Task;

final class FoldTimeEntriesIntoDuration
{
    /** Adds finished entries to task.duration, deletes all entries, returns the seconds added. */
    public function handle(Task $task): int
    {
        $seconds = (int) $task->timeEntries
            ->whereNotNull('ended_at')
            ->sum(fn ($entry) => abs($entry->ended_at->diffInSeconds($entry->started_at)));

        $task->update(['duration' => ($task->duration ?? 0) + $seconds]);

        $task->timeEntries()->delete();

        return $seconds;
    }
}
