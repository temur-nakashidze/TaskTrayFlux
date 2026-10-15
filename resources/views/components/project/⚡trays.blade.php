<?php

use App\Actions\Task\CreateTask;
use App\Contracts\HasTrays;
use App\Enums\TagColor;
use App\Livewire\Concerns\ManagesTrays;
use App\Livewire\Concerns\Tasks\ManagesTasks;
use App\Models\Project;
use App\Models\Task;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Component;

new class extends Component
{
    use ManagesTasks;
    use ManagesTrays;

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
        return $this->owner->tasks()->getQuery();
    }

    #[Computed]
    public function project(): Project
    {
        return $this->owner->owningProject();
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
                'tasks' => fn ($q) => $q->ordered()->with('cuts', 'tags'),
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

    #[Computed]
    public function tagOptions(): array
    {
        return auth()->user()->tags()->orderBy('name')->get(['id', 'name', 'color'])
            ->map(fn ($t) => [
                'id' => $t->id,
                'name' => $t->name,
                'tint' => ($t->color ?? TagColor::Default)->tint(),
                'chip' => ($t->color ?? TagColor::Default)->chip(),
            ])
            ->all();
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

<div>
    <flux:card size="sm" variant="filled" class="flex items-center gap-2 mb-4 p-2">
        <flux:heading class="text-accent font-bold! pl-2" size="xl">Trays</flux:heading>
        <flux:spacer />
        <flux:button icon="archive-box" variant="filled" />
        <flux:button wire:click="addTray" icon="plus" variant="filled">Add Tray</flux:button>

    </flux:card>
    <x-ui.pan-board name="trays" wire:sort="moveTray" wire:transition wire:sort:group="trays"
        class="w-full h-[calc(100vh-12rem)] pb-2" scroll-on="tray-added">

        @foreach ($this->trays as $tray)
            <x-project.tray :tray="$tray" wire:key="trays-tray-{{ $tray->id }}"
                x-sort:item="{{ $tray->id }}" badgeColor="{{ $owner->color }}">
                <div wire:sort="moveTask" wire:sort:group="tray" wire:sort:group-id="{{ $tray->id }}"
                    class="space-y-2 flex-1 min-h-0 overflow-y-auto overflow-x-hidden  px-1 -mx-1">
                    @foreach ($tray->activeTasks as $task)
                        <x-project.task :task="$task" toggleColor="{{ $owner->color }}"
                            wire:key="active-task-{{ $task->id }}" :tags="$task->tags"
                            wire:sort:item="{{ $task->id }}">
                            <x-slot:actions>
                                <flux:button.group>
                                    <flux:button variant="filled" wire:click="completeTask({{ $task->id }})"
                                        icon="check" size="sm" />
                                    <flux:toggle icon="wrench" size="sm" class="rounded-l-none" variant="filled"
                                        icon="wrench-screwdriver" color="{{ $owner->color }}"
                                        x-on:change="$wire.toggleWorkbenchTask({{ $task->id }})"
                                        :checked="$task->workbenchReference" />
                                </flux:button.group>
                            </x-slot:actions>
                        </x-project.task>
                    @endforeach
                    <x-slot:completed>
                        @foreach ($tray->completedTasks as $task)
                            <x-project.task :task="$task" toggleColor="{{ $owner->color }}" completed
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
