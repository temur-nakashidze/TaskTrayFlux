<?php

namespace App\Actions\Task;

use App\Models\Cut;

class CompleteCut
{
    public function handle(Cut $cut)
    {
        $cut->update(['completed_at' => now()]);
    }
}
