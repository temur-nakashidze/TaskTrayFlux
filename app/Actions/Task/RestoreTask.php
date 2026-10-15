<?php

namespace App\Actions\Task;

use App\Models\Task;

final class RestoreTask
{
    public function handle(Task $task): Task
    {
        $task->update([
            'completed_at' => null,
        ]);

        return $task->refresh();
    }
}
