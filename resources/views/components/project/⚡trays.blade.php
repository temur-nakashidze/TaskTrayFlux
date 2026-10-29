<?php

use App\Actions\Task\CreateTask;
use App\Actions\Workbench\RemoveTaskFromWorkbench;
use App\Contracts\HasTrays;
use App\Enums\TagColor;
use App\Livewire\Concerns\ManagesTrays;
use App\Livewire\Concerns\Tasks\ManagesTasks;
use App\Livewire\Concerns\WithWorkbenchPulling;
use App\Models\Project;
use App\Models\Task;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Renderless;
use Livewire\Component;

new class extends Component
{
    use ManagesTasks;
    use ManagesTrays;
    use WithWorkbenchPulling;

    #[Locked]
    public Model $owner;

    public ?int $selectedTask = null;

    public ?string $taskNote = null;

    public array $taskTitles = [];

    public function mount(Model&HasTrays $owner): void
    {
        $this->owner = $owner;
    }

    protected function taskScope(): Builder
    {
        return $this->owner->tasks()->getQuery()->select('tasks.*');
    }

    #[Computed]
    public function project(): Project
    {
        return $this->owner->owningProject();
    }

    protected function pullableTasks(): Builder
    {
        return $this->project->tasks()->notOnWorkbench()->getQuery();
    }

    protected function trayOwner(): Model&HasTrays
    {
        return $this->owner;
    }

    #[Computed]
    public function trays()
    {
        return $this->owner
            ->trays()
            ->ordered('asc')
            ->with([
                'completedTasks' => fn ($q) => $q->ordered()->with('cuts', 'tags'),
                'activeTasks' => fn ($q) => $q->ordered()->with('cuts', 'tags', 'workbenchReference'),
            ])
            ->withCount('activeTasks')
            ->withCount('completedTasks')
            ->get();
    }

    public function addTask(int $trayId, string $title, array $tags = []): void
    {
        $tray = $this->trayOwner()->trays()->findOrFail($trayId);

        app(CreateTask::class)->handle(auth()->user(), $title, tray: $tray, project: $this->project, tags: $tags);
        $this->dispatch('tags-updated');
    }

    #[\Livewire\Attributes\On('tags-updated')]
    public function refreshTags(): void
    {
        unset($this->tagOptions, $this->trays);
    }

    public function openTrash(): void
    {
        $this->dispatch('open-trays-trash', ownerType: $this->owner::class, ownerId: $this->owner->getKey());
    }

    #[Computed]
    public function hasTrashedTrays(): bool
    {
        return $this->owner->trays()->onlyTrashed()->exists();
    }

    #[\Livewire\Attributes\On('trays-restored')]
    public function refreshTrays(): void
    {
        unset($this->trays);
    }

    public function redactingNote(Task $task): void
    {
        $task = $this->taskScope()->findOrFail($task->getKey());
        $this->selectedTask = $task->id;
        $this->taskNote = $task->note;
        $this->modal('update-note')->show();
    }

    public function updateNote(): void
    {
        $task = $this->taskScope()->findOrFail($this->selectedTask);
        $note = $this->taskNote;
        $task->update(['note' => $note]);
        $this->modal('update-note')->close();
    }

    #[Computed]
    public function tagOptions(): array
    {
        return auth()
            ->user()
            ->tags()
            ->orderBy('name')
            ->get(['id', 'name', 'color'])
            ->map(
                fn ($t) => [
                    'id' => $t->id,
                    'name' => $t->name,
                    'tint' => ($t->color ?? TagColor::Default)->tint(),
                    'chip' => ($t->color ?? TagColor::Default)->chip(),
                ],
            )
            ->all();
    }

    public function removeTaskFromWorkbench(int $taskId): void
    {
        $task = $this->taskScope()->findOrFail($taskId);
        app(RemoveTaskFromWorkbench::class)->handle($task->id);
        $this->dispatch('workbench-updated');
    }

    #[Renderless]
    public function toggleWorkbenchTask(int $taskId): void
    {
        $task = $this->project->tasks()->findOrFail($taskId);

        $task->workbenchReference ? $this->removeTaskFromWorkbench($taskId) : $this->pullToWorkbench($task->id, $this->project->workbench->id);
    }

    // #[On('tasks-moved')]
    // #[On('sidebar-reordered')]
    // #[On('tray-created')]
    // #[On('tray-deleted')]
    // #[On('project-updated')]
    // #[On('tasks-restored')]
    public function refresh(): void {}
};
?>

<div class="px-8 py-6">
    @if ($this->trays->isNotEmpty())
        <div class="flex items-start gap-2 mb-4">
            <flux:heading class="font-semibold! font-display" variant="muted" size="lg">Trays</flux:heading>
            <flux:spacer />
            <flux:button wire:click="openTrash" icon="archive-ph-duotone" variant="filled" size="sm"
                aria-label="Open trashed trays" title="Open trash" />
            <flux:button wire:click="addTray" icon="plus" size="sm" variant="filled">Add Tray</flux:button>
        </div>
    @endif

    <x-ui.pan-board name="trays" wire:sort="moveTray" wire:sort:config="{ forceFallback: true }" wire:transition wire:sort:group="trays"
        class="w-full min-h-[calc(100vh-16rem)] pb-2" :class="$this->trays->isEmpty() ? 'items-center justify-center' : ''" scroll-on="tray-added">

        @if ($this->trays->isEmpty())
            <x-ui.empty-state icon="archive-box" heading="No trays yet"
                description="Add a tray to start organizing tasks in this project." class="h-full w-full">
                <x-slot:action>
                    <div class="flex items-center gap-2">
                        <flux:button wire:click="addTray" icon="plus" size="sm" variant="filled">Add first tray
                        </flux:button>
                        @if ($this->hasTrashedTrays)
                            <flux:button wire:click="openTrash" icon="archive-box" variant="filled" size="sm">
                                Open trash
                            </flux:button>
                        @endif
                    </div>
                </x-slot:action>
            </x-ui.empty-state>
        @else
            @foreach ($this->trays as $tray)
                <x-project.tray :tray="$tray" wire:key="trays-tray-{{ $tray->id }}"
                    x-sort:item="{{ $tray->id }}" badgeColor="{{ $this->project->color }}">
                    <div wire:sort="moveTask" wire:sort:config="{ forceFallback: true }" wire:sort:group="tray" wire:sort:group-id="{{ $tray->id }}"
                        class="space-y-2 flex-1 min-h-0 overflow-y-auto overflow-x-hidden px-1 -mx-1">
                        @if ($tray->active_tasks_count === 0 && $tray->completed_tasks_count === 0)
                            <x-ui.empty-state icon="inbox" heading="No tasks in this tray"
                                description="Add a task to get started." class="flex-1 min-h-0" />
                        @else
                            @foreach ($tray->activeTasks as $activeTask)
                                <x-project.task :task="$activeTask" toggleColor="{{ $owner->color }}"
                                    wire:key="active-task-{{ $activeTask->id }}" :tags="$activeTask->tags"
                                    wire:sort:item="{{ $activeTask->id }}">
                                    <x-slot:actions>
                                        <flux:button.group>
                                            <flux:button variant="filled"
                                                wire:click="completeTask({{ $activeTask->id }})" icon="check"
                                                size="sm" />
                                            <flux:toggle size="sm" class="rounded-l-none" variant="filled"
                                                icon="pencil-ruler-ph" color="{{ $this->project->color }}"
                                                x-on:change="$wire.toggleWorkbenchTask({{ $activeTask->id }})"
                                                :checked="$activeTask->workbenchReference" />
                                        </flux:button.group>
                                    </x-slot:actions>
                                </x-project.task>
                            @endforeach
                        @endif
                    </div>
                    <x-slot:completed>
                        @foreach ($tray->completedTasks as $completedTask)
                            <x-project.task :task="$completedTask" completed wire:transition
                                wire:key="completed-task-{{ $completedTask->id }}" :tags="$completedTask->tags"
                                wire:sort:item="{{ $completedTask->id }}" class="" :completed="true"
                                :context="true">
                                <x-slot:actions>
                                    <flux:button variant="filled" icon="arrow-uturn-up"
                                        x-on:click="$wire.restoreTask({{ $completedTask->id }})" iconVariant="outline"
                                        size="sm" />
                                </x-slot:actions>
                            </x-project.task>
                        @endforeach
                    </x-slot:completed>
                </x-project.tray>
            @endforeach
        @endif
    </x-ui.pan-board>
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
</div>
