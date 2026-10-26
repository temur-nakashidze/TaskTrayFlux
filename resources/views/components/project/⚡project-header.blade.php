<?php

use App\Models\Project;
use App\Models\Shelf;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Attributes\Renderless;
use Livewire\Component;

new class extends Component
{
    public Project $project;

    public ?Shelf $shelf = null;

    public bool $onWorkbench = false;

    public bool $onBlueprint = false;

    public string $projectName = '';

    public function mount()
    {
        $this->projectName = $this->project->name;
        $this->onWorkbench = request()->routeIs('workbench');
        $this->onBlueprint = request()->routeIs('blueprint');
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
        $project = auth()->user()->projects()->findOrFail($projectId);
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
<div
    class="sticky top-0 z-30  bg-white/20 dark:bg-zinc-800/10 border-b border-zinc-200/50 dark:border-zinc-800/50 mb-6 flex items-center gap-2 -mx-6 -mt-6 px-4 py-4 backdrop-blur-md">
    @if ($shelf)
        <flux:button href="{{ route('project', $project) }}" wire:navigate icon="arrow-left" variant="subtle"
            aria-label="Back to project" title="Back to project" />
    @endif
    <x-ui.input-ghost wire:key="project-name-{{ $project->id }}" :value="$projectName" maxlength="255"
        x-on:save="$wire.renameProject({{ $project->id }}, $event.detail)"
        class="font-bold! text-zinc-600 dark:text-zinc-50/70 text-2xl font-display uppercase" />
    @if ($shelf)
        <flux:badge color="{{ $project->color }}">{{ $shelf->name ?: 'Untitled shelf' }}</flux:badge>
    @endif
    <flux:spacer />
    <x-project.workbench-button :project="$project" :count="$this->workbenchTasksCount" :active="$onWorkbench" />
    <x-project.blueprint-button :project="$project" :active="$onBlueprint" />
    <div class="flex items-center gap-1 ml-2">
        <flux:button icon="rotate-ccw-clock" variant="subtle" wire:click="$dispatch('open-project-log')" />
        <flux:modal.trigger name="project-settings">
            <flux:button icon="cog-6-tooth" variant="subtle"></flux:button>
        </flux:modal.trigger>
    </div>

    <livewire:project.log />
    <x-project.settings />
</div>
