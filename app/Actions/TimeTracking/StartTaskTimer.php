<?php

namespace App\Actions\TimeTracking;

use App\Models\Task;
use App\Models\TimeEntry;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class StartTaskTimer
{
    public function handle(Task $task, User $user): TimeEntry
    {
        return DB::transaction(function () use ($task, $user) {
            TimeEntry::query()
                ->where('user_id', $user->id)
                ->whereNull('ended_at')
                ->update([
                    'ended_at' => now(),
                ]);

            return TimeEntry::create([
                'user_id' => $user->id,
                'task_id' => $task->id,
                'started_at' => now(),
            ]);
        });
    }
}
