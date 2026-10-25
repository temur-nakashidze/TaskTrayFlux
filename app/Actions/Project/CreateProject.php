<?php

namespace App\Actions\Project;

use App\Models\Project;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class CreateProject
{
    public function handle(User $user, string $name, ?string $color, ?array $trays = null): Project
    {
        $trays ??= config('projects.templates.default.trays');

        return DB::transaction(function () use ($user, $name, $color, $trays) {
            $project = Project::create(['name' => $name, 'user_id' => $user->id, 'color' => $color]);

            foreach (array_reverse($trays) as $trayName) {
                $project->trays()->create(['name' => $trayName]);
            }

            return $project;
        });
    }
}
