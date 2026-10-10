<?php

use App\Actions\Project\CreateProject;
use App\Actions\Project\DeleteProject;
use App\Actions\Workbench\RemoveTaskFromWorkbench;
use App\Models\Project;
use App\Models\Task;
use App\Models\Tray;
use Flux\Flux;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;

new class extends Component
{
    public ?int $deletingId = null;

    public bool $deletingCurrent = false;

    #[Computed]
    public function projects()
    {
        return Project::query()
            ->ordered()
            ->with(['trays' => fn ($q) => $q->ordered()])
            ->withCount('workbenchTasks')
            ->get();
    }

    // Refresh when the board changed something relevant
    #[On('workbench-updated')]
    #[On('board-reordered')]
    public function refresh(): void {}

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
        return $this->deletingId ? Project::withCount(['trays', 'tasks'])->find($this->deletingId) : null;
    }

    public function createProject(string $name, string $template = 'default')
    {
        $name = mb_substr(trim($name), 0, 255);

        if ($name === '') {
            return;
        }

        $trays = config("projects.templates.{$template}.trays");
        abort_if($trays === null, 422);

        $project = app(CreateProject::class)->handle(auth()->user(), $name, $trays);

        return $this->redirectRoute('project', $project, navigate: true);
    }

    public function renameProject(int $id, string $name): void
    {
        $name = mb_substr(trim($name), 0, 255);

        if ($name === '') {
            return;
        }

        Project::findOrFail($id)->update(['name' => $name]);

        $this->dispatch('project-renamed');
    }

    public function confirmDelete(int $id, bool $current = false): void
    {
        $this->deletingId = $id;
        $this->deletingCurrent = $current;

        Flux::modal('delete-project')->show();
    }

    public function deleteProject()
    {
        $project = Project::findOrFail($this->deletingId);

        DB::transaction(fn () => $project->delete());

        $current = $this->deletingCurrent;
        $this->reset('deletingId', 'deletingCurrent');
        unset($this->projects);

        $this->dispatch('timer-changed');
        $this->dispatch('workbench-updated');

        if ($current) {
            return $this->redirectRoute('dashboard', navigate: true);
        }

        Flux::modal('delete-project')->close();
    }
};
?>

<div>
    <flux:sidebar.nav>
        <flux:sidebar.item icon="inbox" badge="3" :href="route('dashboard')" :current="request()->routeIs('dashboard')" wire:navigate>
            Dropzone
        </flux:sidebar.item>
        <flux:sidebar.group :heading="__('Projects')" x-data="newProject({{ \Illuminate\Support\Js::from($this->templates) }})" class="grid mt-4 relative group/sidebar-group">

            <flux:button icon="plus" variant="ghost" size="xs" aria-label="New project" x-on:click="open()"
                class="absolute! right-0 top-1 opacity-0 focus:opacity-100 group-hover/sidebar-group:opacity-100" />

            <flux:card x-show="adding" x-cloak x-collapse.duration.100ms size="sm" class="mb-4">
                <flux:input x-ref="newProject" type="text" maxlength="255" placeholder="Project name"
                    x-on:keydown.enter.prevent="if ($el.value.trim()) $wire.createProject($el.value, template); adding = false"
                    x-on:keydown.escape="adding = false" x-on:keydown.arrow-down.prevent="cycle(1)"
                    x-on:keydown.arrow-up.prevent="cycle(-1)" x-on:blur="adding = false; $el.value = ''"
                    class="w-full" />
                <div class="mt-2 flex flex-wrap gap-2">
                    <template x-for="t in templates" :key="t.key">
                        <button type="button" x-on:mousedown.prevent="template = t.key; $refs.newProject.focus()"
                            x-text="t.label"
                            x-bind:class="template === t.key ?
                                'bg-accent text-white' :
                                'bg-zinc-200/60 text-zinc-600 dark:bg-zinc-700 dark:text-zinc-300'"
                            class="rounded-full px-2 py-0.5 text-sm transition-colors"></button>
                    </template>
                </div>
            </flux:card>
            <div wire:sort="moveProject" wire:sort:group="projects" class="space-y-0.5">
                @foreach ($this->projects as $project)
                    @php
                        $routeProject = request()->route('project');
                        $routeProjectId =
                            $routeProject instanceof \Illuminate\Database\Eloquent\Model
                                ? $routeProject->getKey()
                                : $routeProject;

                        $isCurrent =
                            request()->routeIs('project', 'workbench') &&
                            (string) $routeProjectId === (string) $project->getKey();
                    @endphp

                    <x-ui.context-menu wire:key="project-{{ $project->id }}" wire:sort:item="{{ $project->id }}">

                        <x-ui.sidebar-branch :handle="true" :href="route('project', $project)" :current="$isCurrent" :persist="'sidebar-project-' . $project->id"
                            badgeColor="primary">

                            <x-slot:label>
                                <span x-data="{ editing: false }"
                                    x-on:rename-project.window="if ($event.detail.id === {{ $project->id }}) { editing = true; $nextTick(() => { $refs.name.focus(); $refs.name.select() }) }">

                                    <span x-show="!editing">{{ $project->name }}</span>

                                    <input x-ref="name" x-show="editing" x-cloak type="text" maxlength="255"
                                        value="{{ $project->name }}" x-on:click.prevent.stop
                                        x-on:keydown.enter.prevent="$el.blur()"
                                        x-on:keydown.escape.prevent="$el.value = $el.defaultValue; editing = false"
                                        x-on:blur="
                        if (!editing) return
                        editing = false
                        const v = $el.value.trim()
                        if (v && v !== $el.defaultValue) $wire.renameProject({{ $project->id }}, v)
                        else $el.value = $el.defaultValue
                    "
                                        class="w-full min-w-0 rounded bg-white px-1 text-sm outline-none ring-1 ring-accent dark:bg-zinc-800" />
                                </span>
                            </x-slot:label>

                            <x-slot:badge>
                                <div class="flex items-center gap-2">
                                    <flux:icon.pencil-ruler class="size-4" />
                                    {{ $project->workbench_tasks_count }}
                                </div>
                            </x-slot:badge>

                            {{-- tray reordering --}}
                            <div wire:sort="moveTray" wire:sort:group="sidebar-trays-{{ $project->id }}"
                                class="ml-4 mt-1 mb-2 space-y-1 border-l border-zinc-200 pl-2 dark:border-zinc-700">
                                @foreach ($project->trays as $tray)
                                    <div wire:key="sidebar-tray-{{ $tray->id }}"
                                        wire:sort:item="{{ $tray->id }}"
                                        class="group/stray flex items-center gap-1">

                                        <flux:icon.grip-vertical wire:sort:handle variant="micro"
                                            class="size-3.5 shrink-0 cursor-grab text-zinc-400 opacity-0 transition-opacity group-hover/stray:opacity-70" />

                                        {{-- task drop zone (drop-only) --}}
                                        <div x-data="trayDrop({{ $tray->id }})" x-on:dragenter="enter($event)"
                                            x-on:dragover="enter($event)" x-on:dragleave="leave($event)"
                                            x-on:drop="drop($event)" x-bind:class="over && 'bg-accent/20'"
                                            class="min-h-7 flex-1 rounded-md px-2 py-1 text-sm text-zinc-500 ">
                                            <span class="pointer-events-none">{{ $tray->name }}</span>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </x-ui.sidebar-branch>
                        <x-slot:menu>
                            <x-ui.context-menu.item icon="pencil"
                                x-on:click="$dispatch('rename-project', { id: {{ $project->id }} })">
                                Rename
                            </x-ui.context-menu.item>

                            <x-ui.context-menu.item icon="trash" danger
                                x-on:click="$wire.confirmDelete({{ $project->id }}, {{ $isCurrent ? 'true' : 'false' }})">
                                Delete
                            </x-ui.context-menu.item>
                        </x-slot:menu>
                    </x-ui.context-menu>
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
</div>
