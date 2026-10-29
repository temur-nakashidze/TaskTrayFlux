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
use Illuminate\Database\Eloquent\Builder;
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
        return auth()
            ->user()
            ->projects()
            ->ordered()
            ->with([
                'trays' => fn ($q) => $q->ordered(),
                'shelves' => fn ($q) => $q->ordered(),
            ])
            ->withCount('workbenchTasks')
            ->get();
    }

    public function moveProject(int $id, int $position): void
    {
        auth()->user()->projects()->findOrFail($id)->moveToPosition($position);
    }

    public function projectInitials(string $name): string
    {
        $words = preg_split('/\s+/u', trim($name), -1, PREG_SPLIT_NO_EMPTY) ?: [];

        if (count($words) > 1) {
            return mb_strtoupper(mb_substr($words[0], 0, 1).mb_substr($words[1], 0, 1));
        }

        return mb_strtoupper(mb_substr($words[0] ?? '', 0, 2));
    }

    public function moveShelf(int $id, int $position): void
    {
        $project = auth()->user()->projects()->whereHas('shelves', fn (Builder $query) => $query->whereKey($id))->firstOrFail();

        $project->shelves()->findOrFail($id)->moveToPosition($position, 'asc');
    }

    public function moveTask(int $id, int $position, int $trayId): void
    {
        $user = auth()->user();
        $tray = Tray::query()->forUser($user)->findOrFail($trayId);
        $task = Task::query()->where('user_id', $user->id)->findOrFail($id);

        DB::transaction(function () use ($task, $tray) {
            $projectId = match (true) {
                $tray->trayable instanceof Project => $tray->trayable->id,
                $tray->trayable instanceof Shelf => $tray->trayable->project_id,
                default => abort(404),
            };

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
        return $this->selectedId
            ? auth()
                ->user()
                ->projects()
                ->withCount(['trays', 'tasks'])
                ->find($this->selectedId)
            : null;
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
        $project = auth()->user()->projects()->findOrFail($project->getKey());
        $this->selectedId = $project->id;
        $this->projectName = $project->name;
        $this->projectColor = $project->color->value;
        $this->modal('edit-project')->show();
    }

    public function editProject(): void
    {
        $project = auth()->user()->projects()->findOrFail($this->selectedId);
        app(UpdateProject::class)->handle($project, $this->projectName, $this->projectColor);
        $this->modal('edit-project')->close();
        $this->dispatch('project-updated');
    }

    public function confirmDelete(int $id, bool $current = false): void
    {
        auth()->user()->projects()->findOrFail($id);
        $this->selectedId = $id;
        $this->deletingCurrent = $current;
        $this->modal('delete-project')->show();
    }

    public function deleteProject()
    {
        $project = auth()->user()->projects()->findOrFail($this->selectedId);

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
        $project = auth()->user()->projects()->findOrFail($project->getKey());
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
        <flux:separator variant="subtle" class="mx-3 my-2" />

        <div x-data="newProject({{ \Illuminate\Support\Js::from($this->templates) }})" class="grid group/sidebar-group">
            <div class="mb-1 flex items-center justify-between px-3 in-data-flux-sidebar-collapsed-desktop:hidden">
                <flux:heading size="sm" class="font-medium text-zinc-500 dark:text-zinc-400">{{ __('Projects') }}</flux:heading>
                <flux:modal.trigger name="add-project">
                    <flux:button icon="plus" variant="ghost" size="xs" aria-label="New project"
                        class="opacity-0 focus:opacity-100 group-hover/sidebar-group:opacity-100" />
                </flux:modal.trigger>
            </div>

            <div class="mb-1 hidden justify-center in-data-flux-sidebar-collapsed-desktop:flex">
                <flux:modal.trigger name="add-project">
                    <flux:button icon="plus" variant="ghost" size="sm" square aria-label="New project" title="New project" />
                </flux:modal.trigger>
            </div>

            <div wire:sort="moveProject" wire:sort:config="{ forceFallback: true }" wire:sort:group="projects" class="space-y-2">
                @forelse ($this->projects as $project)
                    @php
                        $isCurrent =
                            in_array($routeName, ['project', 'workbench', 'blueprint', 'shelf'], true) &&
                            $routeProjectId === (int) $project->getKey();
                    @endphp


                    <x-ui.sidebar-branch :handle="true" :href="$isCurrent ? null : route('project', $project)"
                        :compact-href="route('project', $project)" :compact-label="$this->projectInitials($project->name)"
                        :compact-title="$project->name"
                        :compact-color="$project->color->bg().' '.$project->color->border().' '.$project->color->text()"
                        :current="$isCurrent"
                        color="{{ $project->color }}" :item="$project" :workbench="$project->workbench()->exists()" :workbench-active="$isCurrent && $routeName === 'workbench'"
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
                            <div wire:sort="moveShelf" wire:sort:config="{ forceFallback: true }" wire:sort:group="sidebar-trays-{{ $project->id }}"
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
                @empty
                    <x-ui.empty-state icon="panels-top-left" heading="No projects yet"
                        description="Create a project to start organizing your trays and tasks."
                        class="py-6 in-data-flux-sidebar-collapsed-desktop:hidden">
                        <x-slot:action>
                            <flux:modal.trigger name="add-project">
                                <flux:button icon="plus" size="sm" variant="filled">Create project</flux:button>
                            </flux:modal.trigger>
                        </x-slot:action>
                    </x-ui.empty-state>
                @endforelse
            </div>
        </div>
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
