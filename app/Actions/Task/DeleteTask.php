<?php

namespace App\Actions\Task;

use App\Models\Task;

class DeleteTask
{
    public function handle(Task $task)
    {
        $task->delete();
    }
}
