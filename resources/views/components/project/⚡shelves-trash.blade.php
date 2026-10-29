<?php

use App\Models\Project;
use App\Models\Shelf;
use Flux\Flux;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Attributes\On;
use Livewire\Component;

new class extends Component
{
    #[Locked]
    public ?int $projectId = null;

    public array $selected = [];

    #[On('open-shelves-trash')]
    public function open(int $projectId): void
    {
        $project = auth()->user()->projects()->findOrFail($projectId);

        $this->projectId = $project->id;
        $this->selected = [];

        Flux::modal('shelves-trash')->show();
    }

    #[Computed]
    public function trashedShelves(): Collection
    {
        return $this->project
            ? $this->project->shelves()->onlyTrashed()->ordered('asc')->get()
            : collect();
    }

    #[Computed]
    public function project(): ?Project
    {
        return $this->projectId
            ? auth()->user()->projects()->find($this->projectId)
            : null;
    }

    public function restore(): void
    {
        $shelves = $this->requireProject()
            ->shelves()
            ->onlyTrashed()
            ->whereKey($this->selected)
            ->get();

        $shelves->each(fn (Shelf $shelf) => $shelf->restore());
        $this->selected = [];
        unset($this->trashedShelves);

        $this->dispatch('shelves-restored');
    }

    public function forceDelete(): void
    {
        $shelves = $this->requireProject()
            ->shelves()
            ->onlyTrashed()
            ->whereKey($this->selected)
            ->get();

        $shelves->each(fn (Shelf $shelf) => $shelf->forceDelete());
        $this->selected = [];
        unset($this->trashedShelves);
    }

    private function requireProject(): Project
    {
        return $this->project ?? abort(404);
    }
};
?>

<div>
    <flux:modal name="shelves-trash" class="md:w-106" scroll="body">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">Trashed Shelves</flux:heading>
            </div>

            @if ($this->trashedShelves->isEmpty())
                <x-ui.empty-state icon="trash" heading="No deleted shelves"
                    description="Shelves deleted from this project will appear here." />
            @else
                <flux:checkbox.group variant="cards" wire:model.live="selected" class="flex-col text-zinc-600!">
                    <flux:checkbox.all label="select all" />
                    @foreach ($this->trashedShelves as $trashedShelf)
                        <flux:checkbox value="{{ $trashedShelf->id }}" label="{{ $trashedShelf->name }}" />
                    @endforeach
                </flux:checkbox.group>
            @endif
            <div class="flex gap-2">
                <flux:spacer />
                <flux:modal.close>
                    <flux:button variant="ghost">Cancel</flux:button>
                </flux:modal.close>
                <flux:button :disabled="empty($selected)" wire:click="forceDelete" variant="danger">Delete</flux:button>
                <flux:button :disabled="empty($selected)" wire:click="restore" variant="primary">Restore</flux:button>
            </div>
        </div>
    </flux:modal>
</div>
