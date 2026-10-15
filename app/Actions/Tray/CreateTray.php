<?php

namespace App\Actions\Tray;

use App\Contracts\HasTrays;
use App\Models\Tray;
use Illuminate\Database\Eloquent\Model;

class CreateTray
{
    public function handle(Model&HasTrays $parent): Tray
    {
        return $parent->trays()->create();
    }
}
