<?php

namespace App\Actions\Workbench;

use App\Models\Task;
use Illuminate\Support\Facades\DB;

final class RemoveTaskFromActive
{
    public function __construct(private FoldTimeEntriesIntoDuration $fold) {}

    /** @return bool true if the time was discarded, false if it was saved into duration */
    public function handle(Task $task, ?int $discardUnderMinutes = null): bool
    {
        $threshold = ($discardUnderMinutes ?? config('workbench.discard_under_minutes', 2)) * 60;

        return DB::transaction(function () use ($task, $threshold) {
            // Stop a running timer first, otherwise its time is lost
            $task->timeEntries()->whereNull('ended_at')->update(['ended_at' => now()]);

            $task->load('timeEntries');

            $tracked = (int) $task->timeEntries
                ->sum(fn ($entry) => abs($entry->ended_at->diffInSeconds($entry->started_at)));

            if ($tracked < $threshold) {
                $task->timeEntries()->delete();

                return true;
            }

            $this->fold->handle($task);

            return false;
        });
    }
}
