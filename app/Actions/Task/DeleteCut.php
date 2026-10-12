<?php

namespace App\Actions\Task;

use App\Contracts\DeletesRecord;
use App\Models\Cut;

class DeleteCut implements DeletesRecord
{
    public function handle(Cut $cut)
    {
        $cut->delete();
    }
}
