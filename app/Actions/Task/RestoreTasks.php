<?php

namespace App\Actions\Task;

use App\Models\Tray;
use Illuminate\Support\Facades\DB;

class RestoreTasks
{
    public function handle(Tray $tray, array $ids): int
    {
        return DB::transaction(function () use ($tray, $ids) {
            $tasks = $tray->tasks()->onlyTrashed()->whereKey($ids)->get();

            $tasks->each->restore();

            return $tasks->count();
        });
    }
}
