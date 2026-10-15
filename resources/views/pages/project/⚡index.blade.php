<?php

use App\Actions\Task\CreateTask;
use App\Actions\task\DeleteCut;
use App\Actions\Task\DeleteTask;
use App\Actions\Tray\DeleteTray;
use App\Actions\Workbench\RemoveTaskFromWorkbench;
use App\Concerns\Livewire\Tasks\AddsTasks;
use App\Concerns\Livewire\Tasks\HasTasksRoutes;
use App\Livewire\Concerns\WithWorkbenchPulling;
use App\Models\Cut;
use App\Models\Project;
use App\Models\Task;
use App\Models\Tray;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Attributes\Renderless;
use Livewire\Component;

new class extends Component
{
    use WithWorkbenchPulling;

    public Project $project;

    public array $trayNames = [];

    public array $taskTitles = [];

    public string $deletingMessage = 'Are you Shure To delete?';

    public string $deletingDesc = 'Are you Shure To delete?';

    protected array $deletableRecords = [
        'task' => [
            'model' => Task::class,
            'action' => DeleteTask::class,
        ],
        'tray' => [
            'model' => Tray::class,
            'action' => DeleteTray::class,
        ],
        'cut' => [
            'model' => Cut::class,
            'action' => DeleteCut::class,
        ],
    ];

    public ?int $deletingId = null;

    public string $deletingType = '';

    public ?int $selectedTask = null;

    public ?string $taskNote = null;

    public function mount(Project $project): void
    {
        $this->project = $project;
        $this->trayNames = $project->trays()->pluck('name', 'id')->all();
    }

    protected function pullableTasks(): Builder
    {
        return $this->project->tasks()->notOnWorkbench()->getQuery();
    }

    public function confirmDelete(string $type, int $id)
    {
        if (! array_key_exists($type, $this->deletableRecords)) {
            abort(403, 'Invalid entity Type');
        }

        $this->deletingId = $id;
        $this->deletingType = $type;

        $this->modal('delete-record')->show();
    }

    public function delete()
    {
        if (! array_key_exists($this->deletingType, $this->deletableRecords)) {
            abort(403, 'Invalid entity Type');
        }

        $entity = $this->deletableRecords[$this->deletingType];

        $record = $entity['model']::findOrFail($this->deletingId);

        $this->modal('delete-record')->close();
        app($entity['action'])->handle($record);
        // Reset state and close modal
        $this->reset(['deletingId', 'deletingType']);
        $this->dispatch('timer-changed');
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

    #[On('tasks-moved')]
    #[On('sidebar-reordered')]
    #[On('tray-created')]
    #[On('tray-deleted')]
    #[On('project-updated')]
    #[On('tasks-restored')]
    public function refresh(): void {}

    public function render()
    {
        return $this->view()->title($this->project->name);
    }
};
?>

<div>

    <div class="space-y-8">
        <livewire:project.project-header :project="$this->project" />
        <livewire:project.shelves :project="$this->project" />
        <livewire:project.trays :owner="$this->project" />

        <livewire:project.tray-trash />
        <livewire:project.log />
    </div>
</div>
