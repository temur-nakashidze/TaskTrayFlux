<?php

use App\Models\Project;
use App\Models\Shelf;
use Livewire\Attributes\On;
use Livewire\Component;

new class extends Component
{
    public Project $project;

    public Shelf $shelf;

    public function mount(Project $project, Shelf $shelf)
    {
        $this->project = auth()->user()->projects()->findOrFail($project->getKey());
        $this->shelf = $this->project->shelves()->findOrFail($shelf->getKey());
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
    <livewire:project.project-header :project="$this->project" :shelf="$this->shelf" />
    <div class="space-y-2 -mx-6 -mt-6">
        <livewire:project.trays :owner="$this->shelf" />
    </div>
    <livewire:project.trays-trash />
    <livewire:project.tray-trash />


</div>
