<?php

namespace App\Actions\Shelf;

use App\Models\Project;
use App\Models\Shelf;

class CreateShelf
{
    public function handle(Project $project): Shelf
    {

        $shelf = $project->shelves()->create();

        return $shelf;

    }
}
