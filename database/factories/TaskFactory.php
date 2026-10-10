<?php

namespace Database\Factories;

use App\Models\Project;
use App\Models\Shelf;
use App\Models\Task;
use App\Models\Tray;
use App\Models\User;
use Illuminate\Database\Eloquent\Attributes\UseModel;
use Illuminate\Database\Eloquent\Factories\Factory;
use InvalidArgumentException;

#[UseModel(Task::class)]
class TaskFactory extends Factory
{
    public function definition(): array
    {
        return [
            // Defaults to the Dropzone (no project at all).
            "user_id" => User::factory(),
            "project_id" => null,
            "tray_id" => null,
            "title" => ucfirst(fake()->sentence(4)),
            "note" => fake()->boolean(60) ? fake()->paragraph() : null,
            "position" => 0,
        ];
    }

    /**
     * Task sits directly on the Project (no Shelf).
     */
    public function forProject(Project $project): static
    {
        return $this->state(
            fn() => [
                "project_id" => $project->id,
                "tray_id" => null,
            ],
        );
    }

    /**
     * Task sits in a Tray. The Tray must belong to a Project or a Shelf
     * (never a Workbench - workbench placement is a WorkbenchTask reference,
     * not a real task location).
     */
    public function forTray(Tray $tray): static
    {
        return match ($tray->trayable_type) {
            Project::class => $this->state(
                fn() => [
                    "project_id" => $tray->trayable_id,
                    "tray_id" => $tray->id,
                ],
            ),
            default => throw new InvalidArgumentException(
                "A Task cannot be placed directly in a Workbench tray - pull it in via Workbench::pull() instead.",
            ),
        };
    }
}
