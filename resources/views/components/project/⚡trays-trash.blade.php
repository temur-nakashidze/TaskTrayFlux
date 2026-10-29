<?php

use App\Contracts\HasTrays;
use App\Models\Project;
use App\Models\Shelf;
use App\Models\Tray;
use Flux\Flux;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Attributes\On;
use Livewire\Component;

new class extends Component
{
    #[Locked]
    public ?string $ownerType = null;

    #[Locked]
    public ?int $ownerId = null;

    public array $selected = [];

    #[On('open-trays-trash')]
    public function open(string $ownerType, int $ownerId): void
    {
        $this->resolveOwner($ownerType, $ownerId);

        $this->ownerType = $ownerType;
        $this->ownerId = $ownerId;
        $this->selected = [];

        Flux::modal('trays-trash')->show();
    }

    #[Computed]
    public function trashedTrays(): Collection
    {
        $owner = $this->owner();

        return $owner
            ? $owner->trays()->onlyTrashed()->ordered('asc')->get()
            : collect();
    }

    public function restore(): void
    {
        $trays = $this->requireOwner()
            ->trays()
            ->onlyTrashed()
            ->whereKey($this->selected)
            ->get();

        $trays->each(fn (Tray $tray) => $tray->restore());
        $this->selected = [];
        unset($this->trashedTrays);

        $this->dispatch('trays-restored');
    }

    public function forceDelete(): void
    {
        $trays = $this->requireOwner()
            ->trays()
            ->onlyTrashed()
            ->whereKey($this->selected)
            ->get();

        $trays->each(fn (Tray $tray) => $tray->forceDelete());
        $this->selected = [];
        unset($this->trashedTrays);
    }

    private function owner(): (Model&HasTrays)|null
    {
        if (! $this->ownerType || ! $this->ownerId) {
            return null;
        }

        return $this->resolveOwner($this->ownerType, $this->ownerId);
    }

    private function requireOwner(): Model&HasTrays
    {
        return $this->owner() ?? abort(404);
    }

    private function resolveOwner(?string $ownerType, ?int $ownerId): Model&HasTrays
    {
        if (! $ownerType || ! $ownerId) {
            abort(404);
        }

        if ($ownerType === Project::class) {
            return auth()->user()->projects()->findOrFail($ownerId);
        }

        if ($ownerType === Shelf::class) {
            $project = auth()->user()->projects()
                ->whereHas('shelves', fn (Builder $query) => $query->whereKey($ownerId))
                ->firstOrFail();

            return $project->shelves()->findOrFail($ownerId);
        }

        abort(404);
    }
};
?>

<div>
    <flux:modal name="trays-trash" class="md:w-106" scroll="body">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">Trashed Trays</flux:heading>
            </div>

            @if ($this->trashedTrays->isEmpty())
                <x-ui.empty-state icon="trash" heading="No deleted trays"
                    description="Trays deleted from this section will appear here." />
            @else
                <flux:checkbox.group variant="cards" wire:model.live="selected" class="flex-col text-zinc-600!">
                    <flux:checkbox.all label="select all" />
                    @foreach ($this->trashedTrays as $trashedTray)
                        <flux:checkbox value="{{ $trashedTray->id }}" label="{{ $trashedTray->name }}" />
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
