<?php

namespace App\Actions\Tray;

use App\Models\Tray;

class DeleteTray
{
    public function handle(Tray $tray)
    {
        $tray->delete();
    }
}
