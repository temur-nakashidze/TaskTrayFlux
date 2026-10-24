<?php

namespace App\Actions\Project;

use App\Models\Project;

class CreateBlueprint
{
    public function handle(Project $project)
    {
        $blueprintId = $project->blueprint?->id;
        $project->firstOrCreate(['id' => $blueprintId]);
    }
}
