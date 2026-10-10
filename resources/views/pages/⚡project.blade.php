<?php

use App\Actions\Project\CreateTray;
use App\Actions\Project\RemoveTray;
use App\Actions\Workbench\RemoveTaskFromWorkbench;
use App\Concerns\Ui\WithHeader;
use App\Livewire\Concerns\WithWorkbenchPulling;
use App\Models\Cut;
use App\Models\Project;
use App\Models\Task;
use App\Models\Tray;
use App\Models\Workbench;
use App\Models\WorkbenchTask;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Attributes\Renderless;
use Livewire\Component;

new class extends Component
{
    use WithHeader, WithWorkbenchPulling;

    public Project $project;

    public array $trayNames = [];

    public array $taskTitles = [];

    public function mount(Project $project): void
    {
        $this->project = $project;
        $this->trayNames = $project->trays()->pluck('name', 'id')->all();
    }

    protected function pullableTasks(): Builder
    {
        return $this->project->tasks()->notOnWorkbench()->getQuery();
    }

    #[Computed]
    #[On('tray-created')]
    public function trays()
    {
        return $this->project
            ->trays()
            ->ordered()
            ->with(['tasks' => fn ($q) => $q->ordered()->with('cuts', 'tags')])
            ->withCount('tasks')
            ->get();
    }

    public function removeTaskFromWorkbench(int $taskId): void
    {
        $task = Task::findOrFail($taskId);
        app(RemoveTaskFromWorkbench::class)->handle($task->id);
        $this->dispatch('workbench-updated');
    }

    #[Renderless]
    public function toggleWorkbenchTask(int $taskId): void
    {
        $task = $this->project->tasks()->findOrFail($taskId);

        $task->workbenchReference ? $this->removeTaskFromWorkbench($taskId) : $this->pullToWorkbench($task->id, $this->project->workbench->id);
    }

    public function moveTask(int $id, int $position, int $trayId): void
    {
        $task = Task::whereHas('tray', fn ($q) => $q->inProject($this->project))->findOrFail($id);
        $task->moveToTray($this->project->trays()->findOrFail($trayId), $position);
    }

    #[Renderless]
    public function moveTray(int $id, int $position): void
    {
        $tray = Tray::findOrFail($id);
        $tray->moveToPosition($position);
    }

    #[Renderless]
    public function renameTask(int $taskId, string $title): void
    {
        $title = mb_substr(trim($title), 0, 255);

        if ($title === '') {
            return;
        }

        Task::whereHas('tray', fn ($q) => $q->inProject($this->project))
            ->findOrFail($taskId)
            ->update(['title' => $title]);
    }

    #[Renderless]
    public function renameTray(int $trayId, string $value): void
    {
        $tray = $this->project->trays()->findOrFail($trayId);
        $name = mb_substr(trim($value), 0, 255);

        if ($name === '') {
            $this->trayNames[$trayId] = $tray->name; // revert

            return;
        }

        if ($name !== $tray->name) {
            $tray->update(['name' => $name]);
        }

        $this->trayNames[$trayId] = $name;
    }

    public function addTask(int $trayId): void
    {
        $title = trim($this->taskTitles[$trayId] ?? '');

        if ($title === '') {
            return;
        }

        $tray = $this->project->trays()->findOrFail($trayId);

        $tray->tasks()->create([
            'title' => mb_substr($title, 0, 255),
            'user_id' => auth()->id(),
            'project_id' => $this->project->id,
        ]);

        unset($this->taskTitles[$trayId], $this->trays);
    }

    public function addTray()
    {
        app(CreateTray::class)->handle($this->project->id);
        $this->dispatch('trays-updated');
    }

    public function deleteTray(int $trayId)
    {
        app(RemoveTray::class)->handle($trayId);
        $this->dispatch('trays-updated');
    }

    #[Renderless]
    public function renameCut(int $cutId, string $title): void
    {
        $title = mb_substr(trim($title), 0, 255);

        if ($title === '') {
            return;
        }

        Cut::whereHas('task.tray', fn ($q) => $q->inProject($this->project))
            ->findOrFail($cutId)
            ->update(['title' => $title]);
    }

    // A task was dropped in the sidebar: an empty listener is enough, the component re-renders.
    #[On('tasks-moved')]
    #[On('sidebar-reordered')]
    public function refreshAfterMove(): void {}
};
?>

<div>

    <livewire:project-header :project="$this->project" />


    <x-ui.pan-board wire:sort="moveTray" x-sort:group="trays" class="w-full h-[calc(100vh-9rem)] pb-2">

        <flux:button wire:click="addTray" icon="plus" size="sm" variant="filled" class="shrink-0 mb-4" />

        @foreach ($this->trays as $tray)
            <x-project.tray :tray="$tray" wire:transition wire:key="tray-{{ $tray->id }}"
                x-sort:item="{{ $tray->id }}" />
        @endforeach
    </x-ui.pan-board>
</div>
