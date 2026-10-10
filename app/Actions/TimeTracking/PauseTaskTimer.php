<?php

namespace App\Actions\TimeTracking;

use App\Models\TimeEntry;
use App\Models\User;

class PauseTaskTimer
{
    public function handle(User $user): void
    {
        TimeEntry::query()
            ->where('user_id', $user->id)
            ->whereNull('ended_at')
            ->update([
                'ended_at' => now(),
            ]);
    }
}
