<?php

namespace App\Actions\Task;

use App\Models\Cut;

class RestoreCut
{
    public function handle(Cut $cut)
    {
        $cut->update(['completed_at' => null]);
    }
}
