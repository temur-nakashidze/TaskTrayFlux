<?php

namespace App\Livewire\Concerns\Tasks;

use App\Actions\Task\CompleteTask;
use App\Actions\Task\DeleteTask;
use App\Actions\Task\RestoreTask;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\Renderless;

trait ManagesTasks
{
    use ManagesCuts;

    /** The tasks this component is allowed to touch. */
    abstract protected function taskScope(): Builder;

    public function completeTask(int $taskId): void
    {
        $task = $this->taskScope()->findOrFail($taskId);
        app(CompleteTask::class)->handle($task, auth()->user());
        $this->afterTaskChange();
    }

    public function deleteTask(int $taskId): void
    {
        app(DeleteTask::class)->handle($this->taskScope()->findOrFail($taskId));
        $this->afterTaskChange();
    }

    public function restoreTask(int $taskId): void
    {
        app(RestoreTask::class)->handle($this->taskScope()->withTrashed()->findOrFail($taskId));
        $this->afterTaskChange();
    }

    #[Renderless]
    public function renameTask(int $taskId, string $title): void
    {
        $title = mb_substr(trim($title), 0, 255);

        if ($title !== '') {
            $this->taskScope()->findOrFail($taskId)->update(['title' => $title]);
        }
    }

    public function moveTask(int $id, int $position, int $trayId): void
    {

        // TODO: Autorize
        $task = $this->taskScope()->findOrFail($id);
        $task->moveToTray($this->project->trays()->findOrFail($trayId), $position);
    }

    protected function afterTaskChange(): void
    {
        $this->dispatch('tasks-moved'); // the other components refresh
    }
}
