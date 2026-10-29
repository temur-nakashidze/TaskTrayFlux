<?php

use App\Actions\Shelf\CreateShelf;
use App\Models\Project;
use App\Models\Shelf;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Attributes\On;
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

    public function openTrash(): void
    {
        $this->dispatch('open-shelves-trash', projectId: $this->project->id);
    }

    #[Computed]
    public function hasTrashedShelves(): bool
    {
        return $this->project->shelves()->onlyTrashed()->exists();
    }

    #[On('shelves-restored')]
    public function refreshShelves(): void
    {
        unset($this->shelves);
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

<div class="px-8 py-6">
    @if ($this->shelves->isNotEmpty())
        <div class="flex items-start gap-2 mb-4">
            <flux:heading class="font-semibold! font-display" variant="muted" size="lg">Shelves</flux:heading>
            <flux:spacer />
            <flux:button wire:click="openTrash" icon="archive-ph-duotone" size="sm" variant="filled-bordered"
                aria-label="Open trashed shelves" title="Open trash" />
            <flux:button wire:click="addShelf" icon="plus" size="sm" variant="filled-bordered">Add Shelf
            </flux:button>
        </div>
    @endif
    <x-ui.pan-board name="shelves" wire:sort="moveShelf" wire:sort:config="{ forceFallback: true }" wire:transition wire:sort:group="shelves" class="w-full pb-2"
        :class="$this->shelves->isEmpty() ? ' items-center justify-center' : ''" scroll-on="shelf-added">

        @if ($this->shelves->isEmpty())
            <x-ui.empty-state icon="archive-box" heading="No shelves yet"
                description="Create a shelf to group trays and tasks in this project.">
                <x-slot:action>
                    <div class="flex items-center gap-2">
                        <flux:button wire:click="addShelf" icon="plus" size="sm" variant="filled">Add first
                            shelf
                        </flux:button>
                        @if ($this->hasTrashedShelves)
                            <flux:button wire:click="openTrash" icon="archive-box" size="sm" variant="filled">
                                Open trash
                            </flux:button>
                        @endif
                    </div>
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
