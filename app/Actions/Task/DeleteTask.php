<?php

namespace App\Actions\Task;

use App\Contracts\DeletesRecord;
use App\Models\Task;

class DeleteTask implements DeletesRecord
{
    public function handle(Task $task)
    {
        $task->delete();
    }
}
