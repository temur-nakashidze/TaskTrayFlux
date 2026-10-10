<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

#[Table('workbenches')]
class Workbench extends Model
{
    // No SoftDeletes: a Workbench has no independent lifecycle. It is
    // created alongside its Project and only ever removed via the
    // Project's own delete cascade (see ProjectObserver).

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * The pull references themselves - tasks manually pulled into this
     * Workbench from anywhere in the same project.
     */
    public function workbenchTasks(): HasMany
    {
        return $this->hasMany(WorkbenchTask::class);
    }

    public function tasks(): BelongsToMany
    {
        return $this->belongsToMany(Task::class, 'workbench_task')->withPivot(
            'position',
            'id',
        );
    }

    public function pull(Task $task, ?int $position = null): WorkbenchTask
    {
        $this->guardPull($task);

        if (
            $existing = $this->workbenchTasks()
                ->where('task_id', $task->id)
                ->first()
        ) {
            return $existing;
        }

        return DB::transaction(function () use ($task, $position) {
            $reference = $this->workbenchTasks()->create([
                'task_id' => $task->id,
            ]);
            if ($position !== null) {
                $reference->moveToPosition($position);
            }

            return $reference;
        });
    }

    protected function guardPull(Task $task): void
    {
        // the task must be on this workbench's project board
        $onBoard = Task::query()
            ->whereKey($task->id)
            ->whereHas('tray', fn ($q) => $q->inProject($this->project))
            ->exists();

        if (! $onBoard) {
            throw new InvalidArgumentException(
                "Task is not in this workbench's project.",
            );
        }
    }
}
