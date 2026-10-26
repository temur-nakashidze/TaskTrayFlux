<?php

use App\Actions\Task\ForceDeleteTasks;
use App\Actions\Task\RestoreTasks;
use App\Models\Tray;
use Flux\Flux;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Attributes\On;
use Livewire\Component;

new class extends Component
{
    #[Locked]
    public ?int $trayId = null;

    public array $selected = [];

    #[On('open-tray-trash')]
    public function open(int $trayId): void
    {
        $tray = Tray::query()->forUser(auth()->user())->findOrFail($trayId);

        $this->trayId = $tray->id;
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
            ? Tray::query()->forUser(auth()->user())->find($this->trayId)
            : null;
    }

    #[Computed]
    public function trashedTasks(): Collection
    {
        return $this->tray ? $this->tray->tasks()->onlyTrashed()->get() : collect();
    }

    public function restore(RestoreTasks $action): void
    {
        $action->handle($this->tray ?? abort(404), $this->selected);
        $this->reset('selected');
        unset($this->trashedTasks);

        $this->dispatch('tasks-restored');
    }

    public function forceDelete(ForceDeleteTasks $action): void
    {
        $action->handle($this->tray ?? abort(404), $this->selected);
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

            @if ($this->trashedTasks->isEmpty())
                <x-ui.empty-state icon="trash" heading="No deleted tasks"
                    description="Tasks deleted from this tray will appear here." />
            @else
                <flux:checkbox.group variant="cards" wire:model.live="selected" class="flex-col text-zinc-600!">
                    <flux:checkbox.all label="select all" />
                    @foreach ($this->trashedTasks as $trashedTask)
                        <flux:checkbox value="{{ $trashedTask->id }}" label="{{ $trashedTask->title }}" />
                    @endforeach
                </flux:checkbox.group>
            @endif
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
