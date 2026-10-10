<?php

namespace App\Actions\TimeTracking;

use App\Models\TimeEntry;
use App\Models\User;

class ResumeTaskTimer
{
    public function handle(User $user): ?TimeEntry
    {
        $lastEntry = TimeEntry::query()
            ->where('user_id', $user->id)
            ->latest('ended_at')
            ->first();

        if (! $lastEntry) {
            return null;
        }

        return TimeEntry::create([
            'user_id' => $user->id,
            'task_id' => $lastEntry->task_id,
            'started_at' => now(),
        ]);
    }
}
