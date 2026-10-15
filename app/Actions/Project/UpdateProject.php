<?php

namespace App\Actions\Project;

use App\Models\Project;

class UpdateProject
{
    /**
     * Create a new class instance.
     */
    public function handle(Project $project, string $name, ?string $color)
    {
        return $project->update(['name' => $name, 'color' => $color]);
    }
}
