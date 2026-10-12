<?php

namespace App\Actions\Project;

use App\Models\Project;
use App\Models\Tray;

class CreateTray
{
    public function handle(int $projectId): Tray
    {
        $project = Project::findOrFail($projectId);

        $tray = $project->trays()->create();

        return $tray;

    }
}
