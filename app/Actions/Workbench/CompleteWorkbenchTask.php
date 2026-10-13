<?php

namespace App\Actions\Workbench;

use App\Actions\TimeTracking\PauseTaskTimer;
use App\Models\Task;
use App\Models\User;

final class CompleteWorkbenchTask
{
    public function handle(Task $task, User $user): Task
    {
        if ($task->runningTimeEntry) {
            app(PauseTaskTimer::class)->handle($user);

            $task->refresh();
        }

        $task->update([
            'completed_at' => now(),
        ]);

        return $task->refresh();
    }
}
