<?php

use App\Models\Project;
use App\Models\Shelf;
use Livewire\Component;

new class extends Component
{
    public Project $project;

    public Shelf $shelf;

    public function mount(Project $project, Shelf $shelf)
    {
        $this->project = $project;
        $this->shelf = $shelf;
    }
};
?>

<div>
    <livewire:project.project-header :project="$this->project" />

    <livewire:project.trays :owner="$this->shelf" />
</div>
