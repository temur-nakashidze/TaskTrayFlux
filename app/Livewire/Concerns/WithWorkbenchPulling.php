<?php

namespace App\Livewire\Concerns;

use App\Models\Workbench;
use Illuminate\Database\Eloquent\Builder;

/**
 * Pull a task onto a Workbench: creates a WorkbenchTask reference.
 * The real task is NOT moved and keeps its tray.
 */
trait WithWorkbenchPulling
{
    /** Tasks THIS page may pull. The browser only sends ids, so this scope is the guard. */
    abstract protected function pullableTasks(): Builder;

    /**
     * Called with the task id and the target workbench id:
     *   <x-workbench-drop>:  x-sort="$wire.pullToWorkbench($item, {{ $workbench }})"
     *   button / menu:       wire:click="pullToWorkbench({{ $task->id }}, {{ $workbench->id }})"
     */
    public function pullToWorkbench(int|string $id, int $workbenchId): void
    {
        $task = $this->pullableTasks()->findOrFail($id);

        // Gate::authorize('update', $task);  // when policies exist

        // pull() rejects a task that is not on that workbench's project board,
        // so a forged workbench id cannot be used to reach another project.
        Workbench::findOrFail($workbenchId)->pull($task);

        $this->dispatch('workbench-updated');
    }
}
