<?php
use App\Models\Project;
use Livewire\Attributes\On;
use Livewire\Component;

new class extends Component
{
    public Project $project;

    public function mount(Project $project): void
    {
        $this->project = auth()->user()->projects()->findOrFail($project->getKey());
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

    <livewire:project.project-header :project="$this->project" />
    <div class="space-y-2 -mx-6 -mt-6 ">
        <livewire:project.shelves :project="$this->project" />
        <flux:separator variant="muted" />
        <livewire:project.trays :owner="$this->project" />
    </div>
    <livewire:project.shelves-trash />
    <livewire:project.trays-trash />
    <livewire:project.tray-trash />
</div>
