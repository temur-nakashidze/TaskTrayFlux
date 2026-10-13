<?php

use App\Actions\Task\ForceDeleteTasks;
use App\Actions\Task\RestoreTasks;
use App\Models\Tray;
use Flux\Flux;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;

new class extends Component
{
    public ?int $trayId = null;

    public array $selected = [];

    #[On('open-tray-trash')]
    public function open(int $trayId): void
    {
        $this->trayId = $trayId;
        $this->selected = [];

        Flux::modal('tray-trash')->show();
    }

    public function close(): void
    {
        $this->reset('trayId', 'selected');
    }

    #[Computed]
    public function tray(): ?Tray
    {
        return $this->trayId
            ? Tray::query()->whereKey($this->trayId)->first() // scope to the user's/project's trays here
            : null;
    }

    #[Computed]
    public function trashedTasks(): Collection
    {
        return $this->tray ? $this->tray->tasks()->onlyTrashed()->get() : collect();
    }

    public function restore(RestoreTasks $action)
    {
        $action->handle($this->tray, $this->selected);
        $this->reset('selected');
        unset($this->trashedTasks);

        $this->dispatch('tasks-restored');
    }

    public function forceDelete(ForceDeleteTasks $action)
    {
        $action->handle($this->tray, $this->selected);
        $this->reset('selected');
        unset($this->trashedTasks);
    }
};
?>

<div>
    <flux:modal name="tray-trash" class="md:w-106" scroll="body">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">Trashed Tsks</flux:heading>
            </div>

            <flux:checkbox.group variant="cards" wire:model.live="selected" class="flex-col text-zinc-600!">
                <flux:checkbox.all label="select all" />
                @foreach ($this->trashedTasks as $trashedTask)
                    <flux:checkbox value="{{ $trashedTask->id }}" label="{{ $trashedTask->title }}" />
                @endforeach
            </flux:checkbox.group>
            <div class="flex gap-2">
                <flux:spacer />
                <flux:modal.close>
                    <flux:button variant="ghost">Cencel</flux:button>
                </flux:modal.close>
                <flux:button :disabled="empty($selected)" wire:click="forceDelete" variant="danger">Delete</flux:button>
                <flux:button :disabled="empty($selected)" wire:click="restore" variant="primary">Restore</flux:button>
            </div>
        </div>
    </flux:modal>
</div>
