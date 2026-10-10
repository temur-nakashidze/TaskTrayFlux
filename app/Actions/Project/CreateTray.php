<?php

namespace App\Actions\Project;

use App\Models\Project;

class CreateTray
{
    public function handle(int $projectId): void
    {
        $project = Project::findOrFail($projectId);

        $project->trays()->create();
    }
}
