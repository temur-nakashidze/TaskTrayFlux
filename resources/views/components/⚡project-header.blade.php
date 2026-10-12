<?php

use App\Models\Project;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Attributes\Renderless;
use Livewire\Component;

new class extends Component
{
    public Project $project;

    public bool $onWorkbench = false;

    public string $projectName = '';

    public function mount()
    {
        $this->projectName = $this->project->name;
        $this->onWorkbench = request()->routeIs('workbench');
    }

    #[Computed]
    #[On(['workbench-updated', 'trays-updated'])]
    public function workbenchTasksCount(): int
    {
        return $this->project->workbench->workbenchTasks()->count();
    }

    #[Renderless]
    public function renameProject(int $projectId, string $value): void
    {
        $project = Project::findOrFail($projectId);
        $name = mb_substr(trim($value), 0, 255);

        if ($name === '') {
            $this->projectName = $project->name; // revert

            return;
        }

        if ($name !== $project->name) {
            $project->update(['name' => $name]);
        }

        $this->projectName = $name;

        $this->dispatch('project-updated');
    }

    #[On('project-updated')]
    public function refresh(): void {}
};
?>

<div>
    <flux:card variant="filled" class="mb-4 border  rounded-lg pl-6 py-1 pr-1">
        <div class="flex items-center gap-4">
            <x-ui.input-ghost wire:key="project-name-{{ $project->id }}" :value="$projectName" maxlength="255"
                x-on:save="$wire.renameProject({{ $project->id }}, $event.detail)"
                class="font-medium text-zinc-600 text-2xl font-display" />
            <x-project.workbench-button :project="$project" :count="$this->workbenchTasksCount" :active="$onWorkbench" />
            <flux:spacer />
        </div>

    </flux:card>
</div>
