<?php

namespace App\Actions\Task;

use App\Models\Project;
use App\Models\Task;
use App\Models\Tray;
use App\Models\User;
use Illuminate\Support\Facades\DB;

final class CreateTask
{
    /**
     * Creates a task for the given user.
     *
     * Every piece of context is optional: a tray (and through it a project
     * or shelf), a project, tags. Callers must already have checked that
     * the user may use the tray and project they pass in.
     *
     * @param  array<int, array{id: ?int, name: string}>  $tags
     */
    public function handle(
        User $user,
        string $title,
        ?Tray $tray = null,
        ?Project $project = null,
        array $tags = [],
    ): ?Task {
        $title = mb_substr(trim($title), 0, 255);
        if ($title === '') {
            return null;
        }

        return DB::transaction(function () use ($user, $title, $tray, $project, $tags) {
            $attributes = [
                'title' => $title,
                'user_id' => $user->id,
                'project_id' => $project?->id,
            ];

            $task = $tray
                ? $tray->tasks()->create($attributes)
                : Task::create($attributes);

            if ($tags !== []) {
                app(AttachTags::class)->handle($task, $user, $tags);
            }

            return $task;
        });
    }
}
