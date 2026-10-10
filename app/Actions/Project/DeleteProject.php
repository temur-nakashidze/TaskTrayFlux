<?php

namespace App\Actions\Project;

use App\Models\Project;
use Illuminate\Support\Facades\DB;

final class DeleteProject
{
    public function handle(Project $project): void
    {
        DB::transaction(function () use ($project) {
            $project->delete();
        });
    }
}
