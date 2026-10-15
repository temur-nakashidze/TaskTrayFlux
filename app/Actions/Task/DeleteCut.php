<?php

namespace App\Actions\Task;

use App\Models\Cut;

class DeleteCut
{
    public function handle(Cut $cut)
    {
        $cut->delete();
    }
}
