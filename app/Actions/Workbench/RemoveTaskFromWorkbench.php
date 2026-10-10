<?php

namespace App\Actions\Workbench;

use App\Models\WorkbenchTask;

class RemoveTaskFromWorkbench
{
    /**
     * Create a new class instance.
     */
    public function handle(int $taskId)
    {
        WorkbenchTask::query()->where('task_id', $taskId)->delete();
    }
}
