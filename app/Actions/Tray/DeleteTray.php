<?php

namespace App\Actions\Tray;

use App\Contracts\DeletesRecord;
use App\Models\Tray;

class DeleteTray implements DeletesRecord
{
    public function handle(Tray $tray)
    {
        $tray->delete();
    }
}
