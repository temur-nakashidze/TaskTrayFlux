<?php

use App\Models\Blueprint;
use App\Models\Project;
use Livewire\Attributes\Locked;
use Livewire\Component;

new class extends Component
{
    #[Locked]
    public Project $project;

    #[Locked]
    public Blueprint $blueprint;

    public ?string $blueprintText = '';

    public function mount(Project $project)
    {
        $this->project = $project;
        if (! $project->blueprint) {
            redirect('dashboard');
        }
        $blueprint = $project->blueprint;
        $this->blueprint = $blueprint;

        $this->blueprintText = $blueprint->text;
    }

    public function updatedBlueprintText(): void
    {
        $this->blueprint->update([
            'text' => $this->blueprintText,
        ]);
    }
};
?>

<div>
    <livewire:project.project-header :project="$project" />

    <flux:card variant="filled">
        <x-ui.textarea-ghost wire:model.live="blueprintText">dsadas
        </x-ui.textarea-ghost>
    </flux:card>
</div>
