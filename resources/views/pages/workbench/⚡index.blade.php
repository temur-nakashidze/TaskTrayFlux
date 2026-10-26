<?php

use App\Actions\Task\RestoreTask;
use App\Actions\TimeTracking\PauseTaskTimer;
use App\Actions\TimeTracking\StartTaskTimer;
use App\Actions\Workbench\ClearCompletedTasks;
use App\Actions\Workbench\CompleteWorkbenchTask;
use App\Actions\Workbench\RemoveTaskFromActive;
use App\Actions\Workbench\RemoveTaskFromWorkbench;
use App\Livewire\Concerns\Tasks\ManagesTasks;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use App\Models\Workbench;
use App\Models\WorkbenchTask;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;

new class extends Component
{
    use ManagesTasks;

    public Workbench $workbench;

    public Project $project;

    public User $user;

    public function mount(Project $project)
    {
        $this->project = auth()->user()->projects()->findOrFail($project->getKey());
        $this->workbench = $this->project->workbench()->firstOrFail();
        $this->user = auth()->user();
    }

    #[Computed]
    public function workbenchTasks()
    {
        return $this->workbench
            ->workbenchTasks()
            ->with(['task', 'task.cuts', 'task.tags', 'task.timeEntries', 'task.runningTimeEntry'])
            ->ordered()
            ->get();
    }

    public function taskScope(): Builder
    {
        return Task::onWorkbench($this->workbench);
    }

    #[Computed]
    public function unplayedTasks()
    {
        return $this->workbenchTasks->filter(fn (WorkbenchTask $wt) => $wt->task->timeEntries->isEmpty());
    }

    #[Computed]
    public function playedTasks()
    {
        return $this->workbenchTasks->filter(fn (WorkbenchTask $wt) => $wt->task->timeEntries->isNotEmpty() && $wt->task->completed_at === null);
    }

    #[Computed]
    public function completedTasks()
    {
        return $this->workbenchTasks->filter(fn (WorkbenchTask $wt) => $wt->task->completed_at !== null);
    }

    #[Computed]
    public function runningTask(): ?Task
    {
        return $this->workbenchTasks->first(fn (WorkbenchTask $wt) => $wt->task->runningTimeEntry)?->task;
    }

    public function toggleTimer(WorkbenchTask $workbenchTask): void
    {
        $workbenchTask = $this->workbench->workbenchTasks()->findOrFail($workbenchTask->getKey());
        $task = $workbenchTask->task;

        if ($task->runningTimeEntry) {
            app(PauseTaskTimer::class)->handle($this->user);
        } else {
            app(StartTaskTimer::class)->handle($task, $this->user);
        }

        unset($this->workbenchTasks);
        $this->dispatch('timer-changed');
    }

    public function toggleGlobalTimer(): void
    {
        $runningEntry = $this->user->timeEntries()->whereNull('ended_at')->first();

        if ($runningEntry) {
            app(PauseTaskTimer::class)->handle($this->user);

            unset($this->workbenchTasks);

            return;
        }

        $lastEntry = $this->user->timeEntries()->whereNotNull('ended_at')->latest('started_at')->first();

        if (! $lastEntry) {
            return;
        }

        app(StartTaskTimer::class)->handle($lastEntry->task, $this->user);

        unset($this->workbenchTasks);
        $this->dispatch('timer-changed');
    }

    #[On('close-workbench')]
    public function closeWorkbench()
    {
        if ($this->user->timeEntries()->whereNull('ended_at')->exists()) {
            app(PauseTaskTimer::class)->handle($this->user);
        }

        $this->dispatch('timer-changed');

        return $this->redirectRoute('project', $this->workbench->project, navigate: true);
    }

    public function completeTask(WorkbenchTask $workbenchTask)
    {
        $workbenchTask = $this->workbench->workbenchTasks()->findOrFail($workbenchTask->getKey());
        app(CompleteWorkbenchTask::class)->handle($workbenchTask->task, $this->user);
        $this->dispatch('timer-changed');
    }

    public function restoreTask(WorkbenchTask $workbenchTask)
    {
        $workbenchTask = $this->workbench->workbenchTasks()->findOrFail($workbenchTask->getKey());
        app(RestoreTask::class)->handle($workbenchTask->task);
    }

    public function clearCompleted(): void
    {
        app(ClearCompletedTasks::class)->handle($this->workbench);

        unset($this->workbenchTasks);
        $this->dispatch('timer-changed');
    }

    public function removeFromActive(WorkbenchTask $workbenchTask): void
    {
        $workbenchTask = $this->workbench->workbenchTasks()->findOrFail($workbenchTask->getKey());
        app(RemoveTaskFromActive::class)->handle($workbenchTask->task);

        unset($this->workbenchTasks);
        $this->dispatch('timer-changed');
    }

    public function removeTask(int $taskId)
    {
        $this->workbench->workbenchTasks()->where('task_id', $taskId)->firstOrFail();
        app(RemoveTaskFromWorkbench::class)->handle($taskId);
        $this->dispatch('workbench-updated');
        $this->dispatch('timer-changed');
    }

    #[On('project-updated')]
    public function refresh() {}
};
?>

<div>
    <livewire:project.project-header :project="$this->project" />
    <div class="w-full h-[calc(100vh-12rem)] pb-4 grid grid-cols-2 gap-8">
        <div class="flex flex-col gap-8 h-full">
            @include('pages/workbench/started-tasks')

            @include('pages/workbench/completed-tasks')
        </div>
        @include('pages/workbench/todo-tasks')
    </div>
</div>
