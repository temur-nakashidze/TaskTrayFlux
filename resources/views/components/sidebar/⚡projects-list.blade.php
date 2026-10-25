<?php

use App\Actions\Project\CreateBlueprint;
use App\Actions\Project\CreateProject;
use App\Actions\Project\DeleteProject;
use App\Actions\Project\UpdateProject;
use App\Actions\Workbench\RemoveTaskFromWorkbench;
use App\Models\Project;
use App\Models\Task;
use App\Models\Tray;
use Flux\Flux;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Attributes\On;
use Livewire\Attributes\Validate;
use Livewire\Component;

new class extends Component
{
    public ?int $selectedId = null;

    public bool $deletingCurrent = false;

    #[Locked]
    public ?string $routeName = null;

    #[Locked]
    public ?int $routeProjectId = null;

    public string $selectedTemplate = 'default';

    #[Validate('string|min:2')]
    public string $projectName = '';

    public ?string $projectColor = null;

    public function mount(): void
    {
        $this->routeName = request()->route()?->getName();

        $project = request()->route('project');

        $this->routeProjectId = match (true) {
            $project instanceof Model => (int) $project->getKey(),
            $project !== null => (int) $project,
            default => null,
        };
    }

    #[Computed]
    public function projects()
    {
        return Project::query()
            ->ordered()
            ->with(['trays' => fn ($q) => $q->ordered()])
            ->withCount('workbenchTasks')
            ->get();
    }

    public function moveProject(int $id, int $position): void
    {
        Project::findOrFail($id)->moveToPosition($position);
    }

    public function moveTray(int $id, int $position): void
    {
        Tray::findOrFail($id)->moveToPosition($position);
    }

    public function moveTask(int $id, int $position, int $trayId): void
    {
        $tray = Tray::findOrFail($trayId);
        $task = Task::findOrFail($id);

        DB::transaction(function () use ($task, $tray) {
            $projectId = $tray->trayable_id; // trays are polymorphic, there is no project_id

            if ($task->project_id !== $projectId) {
                if ($task->workbenchReference) {
                    app(RemoveTaskFromWorkbench::class)->handle($task->id);
                }

                $task->update(['project_id' => $projectId]);
            }

            $task->moveToTray($tray, 0);
        });

        $this->dispatch('tasks-moved');
    }

    #[Computed]
    public function templates(): array
    {
        return collect(config('projects.templates'))->map(fn ($t, $key) => ['key' => $key, 'label' => $t['label']])->values()->all();
    }

    #[Computed]
    public function deleting(): ?Project
    {
        return $this->selectedId ? Project::withCount(['trays', 'tasks'])->find($this->selectedId) : null;
    }

    public function createProject()
    {
        $this->validate();
        $name = $this->projectName;
        $template = $this->selectedTemplate;

        $trays = config("projects.templates.{$template}.trays");
        abort_if($trays === null, 422);

        $project = app(CreateProject::class)->handle(auth()->user(), $name, $this->projectColor, $trays);

        return $this->redirectRoute('project', $project, navigate: true);
    }

    public function setProject(Project $project)
    {
        $this->selectedId = $project->id;
        $this->projectName = $project->name;
        $this->projectColor = $project->color->value;
        $this->modal('edit-project')->show();
    }

    public function editProject(): void
    {
        $project = Project::findOrFail($this->selectedId);
        app(UpdateProject::class)->handle($project, $this->projectName, $this->projectColor);
        $this->modal('edit-project')->close();
        $this->dispatch('project-updated');
    }

    public function confirmDelete(int $id, bool $current = false): void
    {
        $this->selectedId = $id;
        $this->deletingCurrent = $current;
        $this->modal('delete-project')->show();
    }

    public function deleteProject()
    {
        $project = Project::findOrFail($this->selectedId);

        app(DeleteProject::class)->handle($project);

        $current = $this->deletingCurrent;
        $this->reset('selectedId', 'deletingCurrent');
        unset($this->projects);

        if ($current) {
            return $this->redirectRoute('dashboard', navigate: true);
        }
        $this->dispatch('timer-changed');
        $this->dispatch('workbench-updated');
        $this->modal('delete-project')->close();
    }

    public function addBlueprint(Project $project)
    {
        app(CreateBlueprint::class)->handle($project);
    }

    // Refresh when the board changed something relevant
    #[On('workbench-updated')]
    #[On('board-reordered')]
    #[On('board-reordered')]
    public function refresh(): void {}
};
?>

<div>
    <flux:sidebar.nav>
        <flux:sidebar.item class="font-display!" icon="inbox" badge="3" :href="route('dashboard')" :current="$routeName === 'dashboard'"
            wire:navigate>
            Dropzone
        </flux:sidebar.item>
        <flux:sidebar.group :heading="__('Projects')" x-data="newProject({{ \Illuminate\Support\Js::from($this->templates) }})" class="grid mt-4 relative group/sidebar-group">

            <flux:modal.trigger name="add-project">
                <flux:button icon="plus" variant="ghost" size="xs" aria-label="New project"
                    class="absolute! right-0 top-1 opacity-0 focus:opacity-100 group-hover/sidebar-group:opacity-100" />
            </flux:modal.trigger>


            <div wire:sort="moveProject" wire:sort:group="projects" class="space-y-2">
                @foreach ($this->projects as $project)
                    @php
                        $isCurrent =
                            in_array($routeName, ['project', 'workbench'], true) &&
                            $routeProjectId === (int) $project->getKey();
                    @endphp


                    <x-ui.sidebar-branch :handle="true" :href="route('project', $project)" :current="$isCurrent"
                        color="{{ $project->color }}" :item="$project" :workbench="$project->workbench()->exists()" :blueprint="$project->blueprint()->exists()"
                        :expandable="$project->shelves()->exists()" context>
                        <x-slot:label>
                            {{ $project->name }}
                        </x-slot:label>
                        <x-slot:context-menu>
                            <x-ui.context-menu.item icon="square-dashed-text"
                                wire:click="addBlueprint({{ $project->id }})">
                                Create Blueprint
                            </x-ui.context-menu.item>
                            <x-ui.context-menu.item icon="pencil" wire:click="setProject({{ $project->id }})">
                                Edit
                            </x-ui.context-menu.item>

                            <x-ui.context-menu.item icon="trash" danger
                                x-on:click="$wire.confirmDelete({{ $project->id }}, {{ $isCurrent ? 'true' : 'false' }})">
                                Delete
                            </x-ui.context-menu.item>
                        </x-slot:context-menu>

                        @if ($project->shelves()->exists())
                            <div wire:sort="moveTray" wire:sort:group="sidebar-trays-{{ $project->id }}"
                                class="mt-2 space-y-1.5 mb-1">
                                @foreach ($project->shelves as $shelf)
                                    <div wire:key="sidebar-shelf-{{ $shelf->id }}"
                                        wire:sort:item="{{ $shelf->id }}"
                                        class="group/shelf flex items-center gap-1 bg-zinc-100/40 dark:bg-zinc-950 border border-zinc-100/80 dark:border-zinc-50/10 rounded-xs px-2">
                                        <x-ui.handle parent="shelf" />

                                        {{-- task drop zone (drop-only) --}}
                                        <div x-data="trayDrop({{ $shelf->id }})" x-on:dragenter="enter($event)"
                                            x-on:dragover="enter($event)" x-on:dragleave="leave($event)"
                                            x-on:drop="drop($event)" x-bind:class="over && 'bg-accent/20'"
                                            class="min-h-7 flex-1 rounded-md py-1.5 text-sm text-zinc-500 dark:text-zinc-50/70">
                                            <span class="pointer-events-none">{{ $shelf->name ?? 'Untitled' }}</span>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </x-ui.sidebar-branch>
                @endforeach
            </div>
        </flux:sidebar.group>
    </flux:sidebar.nav>


    <flux:modal name="delete-project" class="min-w-88">
        @if ($this->deleting)
            <div class="space-y-4">
                <flux:heading size="lg">Delete "{{ $this->deleting->name }}"?</flux:heading>
                <flux:text>
                    This permanently deletes {{ $this->deleting->trays_count }} trays and
                    {{ $this->deleting->tasks_count }} tasks, including their time entries.
                    This can't be undone.
                </flux:text>
                <div class="flex gap-2">
                    <flux:spacer />
                    <flux:modal.close>
                        <flux:button variant="ghost">Cancel</flux:button>
                    </flux:modal.close>
                    <flux:button variant="danger" wire:click="deleteProject">Delete</flux:button>
                </div>
            </div>
        @endif
    </flux:modal>

    @include('components.sidebar.edit-modal')
    @include('components.sidebar.create-modal')
</div>
