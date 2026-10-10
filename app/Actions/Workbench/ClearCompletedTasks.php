<?php

namespace App\Actions\Workbench;

use App\Models\Workbench;
use Illuminate\Support\Facades\DB;

final class ClearCompletedTasks
{
    public function __construct(private FoldTimeEntriesIntoDuration $fold) {}

    public function handle(Workbench $workbench): int
    {
        return DB::transaction(function () use ($workbench) {
            $workbenchTasks = $workbench->workbenchTasks()
                ->with('task.timeEntries')
                ->whereHas(
                    'task',
                    fn ($query) => $query->whereNotNull('completed_at')
                )
                ->get();

            foreach ($workbenchTasks as $workbenchTask) {
                $this->fold->handle($workbenchTask->task);
            }

            $count = $workbenchTasks->count();

            $workbenchTasks->each->delete();

            return $count;
        });
    }
}
