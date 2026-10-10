<?php

namespace App\Actions\Project;

use App\Models\Tray;

class RemoveTray
{
    public function handle(int $trayId)
    {
        $tray = Tray::findOrFail($trayId);
        $tray->delete();
    }
}
