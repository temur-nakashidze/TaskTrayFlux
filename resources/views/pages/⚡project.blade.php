<?php

use App\Actions\Project\CreateTray;
use App\Actions\Project\RemoveTray;
use App\Actions\Task\CompleteCut;
use App\Actions\Task\CompleteTask;
use App\Actions\task\DeleteCut;
use App\Actions\Task\DeleteTask;
use App\Actions\Task\RestoreCut;
use App\Actions\Task\RestoreTask;
use App\Actions\Tray\DeleteTray;
use App\Actions\Workbench\RemoveTaskFromWorkbench;
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

    #[Computed]
    public function trays()
    {
        return $this->project
            ->trays()
            ->ordered('asc')
            ->with([
                'tasks' => fn ($q) => $q->ordered()->with('cuts', 'tags'),
                'completedTasks' => fn ($q) => $q->ordered()->with('cuts', 'tags'),
                'activeTasks' => fn ($q) => $q->ordered()->with('cuts', 'tags'),
            ])
            ->withCount('activeTasks')
            ->withCount('completedTasks')
            ->get();
    }

    #[Renderless]
    public function moveTray(int $id, int $position): void
    {
        $tray = Tray::findOrFail($id);
        $tray->moveToPosition($position, 'asc');
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

    public function addTray()
    {
        $tray = app(CreateTray::class)->handle($this->project->id);
        $this->dispatch('trays-updated');
        $this->dispatch('tray-added', id: $tray->id);
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

    // Task
    #[Computed]
    public function getTrashedTasks(Tray $tray)
    {
        return $tray->tasks->onlyTrashed()->get();
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

    public function deleteTask(Task $task)
    {
        app(DeleteTask::class)->handle($task);
    }

    public function completeTask(Task $task)
    {
        app(CompleteTask::class)->handle($task, auth()->user());
    }

    public function restoreTask(Task $task)
    {
        app(RestoreTask::class)->handle($task);
    }

    // Cuts

    public function addCut(Task $task)
    {
        $task->cuts()->create();
    }

    public function completeCut(Cut $cut)
    {
        app(CompleteCut::class)->handle($cut);
    }

    public function restoreCut(Cut $cut)
    {
        app(RestoreCut::class)->handle($cut);
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

    #[Renderless]
    public function moveCut(Cut $cut, int $position): void
    {
        $cut->moveToPosition($position, 'asc');
    }

    public function redactingNote(Task $task)
    {
        $this->selectedTask = $task->id;
        $this->taskNote = $task->note;
        $this->modal('update-note')->show();
    }

    public function updateNote()
    {
        $task = Task::findOrFail($this->selectedTask);
        $note = $this->taskNote;
        $task->update(['note' => $note]);
        $this->modal('update-note')->close();
    }

    #[On('tasks-moved')]
    #[On('sidebar-reordered')]
    #[On('tray-created')]
    #[On('tray-deleted')]
    #[On('project-updated')]
    #[On('tasks-restored')]
    public function refresh(): void {}
};
?>

<div>

    <livewire:project-header :project="$this->project" />

    <div class="flex items-center gap-2 mb-4">
        <flux:spacer />
        <flux:button icon="archive-box" size="sm" variant="filled" />
        <flux:button wire:click="addTray" icon="plus" size="sm" variant="filled">Add Tray</flux:button>

    </div>
    <x-ui.pan-board wire:sort="moveTray" x-sort:group="trays" class="w-full h-[calc(100vh-12rem)] pb-2 pin-board">

        @foreach ($this->trays as $tray)
            <x-project.tray :tray="$tray" wire:transition wire:key="tray-{{ $tray->id }}"
                x-sort:item="{{ $tray->id }}" badgeColor="{{ $project->color }}">
                <div wire:sort="moveTask" wire:sort:group="tray" wire:sort:group-id="{{ $tray->id }}"
                    class="space-y-2 flex-1 min-h-0 overflow-y-auto overflow-x-hidden overscroll-contain px-1 -mx-1">
                    @foreach ($tray->activeTasks as $task)
                        <x-project.task :task="$task" toggleColor="{{ $project->color }}" wire:transition
                            wire:key="active-task-{{ $task->id }}" :tags="$task->tags"
                            wire:sort:item="{{ $task->id }}">
                            <x-slot:actions>
                                <flux:button.group>
                                    <flux:button variant="filled" wire:click="completeTask({{ $task->id }})"
                                        icon="check" size="sm" />
                                    <flux:toggle icon="wrench" size="sm" class="rounded-l-none" variant="filled"
                                        icon="wrench-screwdriver" color="{{ $project->color }}"
                                        x-on:change="$wire.toggleWorkbenchTask({{ $task->id }})"
                                        :checked="$task->workbenchReference" />
                                </flux:button.group>
                            </x-slot:actions>
                        </x-project.task>
                    @endforeach
                    <x-slot:completed>
                        @foreach ($tray->completedTasks as $task)
                            <x-project.task :task="$task" toggleColor="{{ $project->color }}" completed
                                wire:transition wire:key="completed-task-{{ $task->id }}" :tags="$task->tags"
                                wire:sort:item="{{ $task->id }}" class="" :completed="true"
                                :context="true">
                                <x-slot:actions>
                                    <flux:button variant="filled" icon="arrow-uturn-up"
                                        x-on:click="$wire.restoreTask({{ $task->id }})" iconVariant="outline"
                                        size="sm" />
                                </x-slot:actions>
                            </x-project.task>
                        @endforeach
                    </x-slot:completed>
                </div>
            </x-project.tray>
        @endforeach
    </x-ui.pan-board>


    <flux:modal name="delete-record" class="min-w-[22rem]">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">{{ $deletingMessage }}</flux:heading>

                <flux:text class="mt-2">
                    {{ $deletingDesc }}<br>
                </flux:text>
            </div>

            <div class="flex gap-2">
                <flux:spacer />

                <flux:modal.close>
                    <flux:button variant="ghost">Cancel</flux:button>
                </flux:modal.close>

                <flux:button type="submit" variant="danger" wire:click="delete">Delete</flux:button>
            </div>
        </div>
    </flux:modal>
    <flux:modal name="update-note" class="min-w-100">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">Add Note</flux:heading>
            </div>

            <flux:textarea spellcheck="false" rows="8" autofocus label="Task note" wire:model="taskNote">
            </flux:textarea>
            <div class="flex gap-2">
                <flux:spacer />

                <flux:modal.close>
                    <flux:button variant="ghost">Cancel</flux:button>
                </flux:modal.close>

                <flux:button type="submit" variant="primary" wire:click="updateNote">Save
                </flux:button>
            </div>
        </div>
    </flux:modal>
    <livewire:project.tray-trash />
    <livewire:project.log />
</div>
