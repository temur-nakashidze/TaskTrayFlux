<?php

namespace App\Actions\Task;

use App\Actions\TimeTracking\PauseTaskTimer;
use App\Actions\Workbench\FoldTimeEntriesIntoDuration;
use App\Models\Task;
use App\Models\User;
use Illuminate\Support\Facades\DB;

final class CompleteTask
{
    public function __construct(private FoldTimeEntriesIntoDuration $fold) {}

    public function handle(Task $task, User $user): Task
    {

        return DB::transaction(function () use ($task, $user) {

            if ($task->runningTimeEntry) {
                app(PauseTaskTimer::class)->handle($user);
                $task->refresh();
            }
            $this->fold->handle($task);

            $task->workbenchTask?->delete();

            $now = now();

            $task->cuts()->whereNull('completed_at')
                ->update(['completed_at' => $now]);

            $task->update([
                'completed_at' => $now,
            ]);

            return $task;
        });

    }
}
