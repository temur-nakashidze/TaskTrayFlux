<?php

namespace App\Observers;

use App\Models\Shelf;
use App\Models\Tray;

class ShelfObserver
{
    public function deleting(Shelf $shelf): void
    {
        $shelf->trays()->get()->each(fn (Tray $tray) => $tray->delete());
    }

    public function restoring(Shelf $shelf): void
    {
        $shelf->trays()->onlyTrashed()->get()->each(fn (Tray $tray) => $tray->restore());
    }

    public function forceDeleting(Shelf $shelf): void
    {
        $shelf->trays()->withTrashed()->get()->each(fn (Tray $tray) => $tray->forceDelete());
    }
}
