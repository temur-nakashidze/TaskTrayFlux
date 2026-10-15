<?php

namespace App\Actions\Task;

use App\Models\Tray;

class ForceDeleteTasks
{
    public function handle(Tray $tray, array $ids)
    {

        return $tasks = $tray->tasks()->onlyTrashed()->whereKey($ids)->forceDelete();

    }
}
