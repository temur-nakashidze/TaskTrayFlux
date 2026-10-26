<?php

use App\Actions\Shelf\CreateShelf;
use App\Models\Project;
use App\Models\Shelf;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Renderless;
use Livewire\Component;

new class extends Component
{
    #[Locked]
    public Project $project;

    #[Computed]
    public function shelves()
    {
        return $this->project
            ->shelves()
            ->withCount(['tasks', 'tasks as completed_tasks_count' => fn ($q) => $q->whereNotNull('tasks.completed_at')])
            ->ordered('asc')
            ->get();
    }

    public function addShelf(CreateShelf $action): void
    {
        $shelf = $action->handle($this->project);

        $this->dispatch('shelf-added', id: $shelf->id);
    }

    #[Renderless]
    public function renameShelf(int $shelfId, string $value): void
    {
        $name = mb_substr(trim($value), 0, 255);

        if ($name !== '') {
            $this->project
                ->shelves()
                ->findOrFail($shelfId)
                ->update(['name' => $name]);
        }
    }

    #[Renderless]
    public function moveShelf(int $id, int $position): void
    {
        $this->project->shelves()->findOrFail($id)->moveToPosition($position, 'asc');
    }

    public function deleteShelf(Shelf $shelf): void
    {
        $shelf = $this->project->shelves()->findOrFail($shelf->getKey());
        $shelf->delete();
    }
};
?>

<div class="p-6">
    <div class="flex items-start gap-2 mb-4">
        <flux:heading class="font-semibold! font-display" variant="muted" size="lg">Shelves</flux:heading>
        <flux:spacer />
        <flux:button icon="archive-box" size="sm" variant="filled" />
        <flux:button wire:click="addShelf" icon="plus" size="sm" variant="filled">Add Shelf</flux:button>
    </div>
    <x-ui.pan-board name="shelves" wire:sort="moveShelf" wire:transition wire:sort:group="shelves" class="w-full pb-2"
        scroll-on="shelf-added">

        @if ($this->shelves->isEmpty())
            <x-ui.empty-state icon="archive-box" heading="No shelves yet"
                description="Create a shelf to group trays and tasks in this project.">
                <x-slot:action>
                    <flux:button wire:click="addShelf" icon="plus" size="sm" variant="filled">Add first shelf</flux:button>
                </x-slot:action>
            </x-ui.empty-state>
        @else
            @foreach ($this->shelves as $shelf)
                <x-project.shelf wire:sort:item="{{ $shelf->id }}" :color="$this->project->color->value" :shelf="$shelf"
                    wire:key="shelves-shelf-{{ $shelf->id }}"></x-project.shelf>
            @endforeach
        @endif
    </x-ui.pan-board>
</div>
