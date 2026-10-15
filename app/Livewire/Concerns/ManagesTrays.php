<?php

namespace App\Livewire\Concerns;

use App\Actions\Tray\CreateTray;
use App\Actions\Tray\DeleteTray;
use App\Contracts\HasTrays;
use App\Models\Tray;
use Illuminate\Database\Eloquent\Model;
use Livewire\Attributes\Renderless;

trait ManagesTrays
{
    /** The model that owns the trays this component shows. */
    abstract protected function trayOwner(): Model&HasTrays;

    #[Renderless]
    public function moveTray(int $id, int $position): void
    {
        $this->trayOwner()->trays()->findOrFail($id)->moveToPosition($position, 'asc');
    }

    #[Renderless]
    public function renameTray(int $trayId, string $value): void
    {
        $name = mb_substr(trim($value), 0, 255);

        if ($name !== '') {
            $this->trayOwner()->trays()->findOrFail($trayId)->update(['name' => $name]);
        }
    }

    public function addTray(): void
    {
        $tray = app(CreateTray::class)->handle($this->trayOwner());

        $this->dispatch('trays-updated');
        $this->dispatch('tray-added', id: $tray->id);
    }

    public function deleteTray(Tray $tray): void
    {
        app(DeleteTray::class)->handle($tray);
        $this->dispatch('tray-deleted', id: $tray->id);
    }
}
