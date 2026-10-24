<?php

namespace Database\Factories;

use App\Models\Project;
use App\Models\Tray;
use Illuminate\Database\Eloquent\Factories\Attributes\UseModel;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;

#[UseModel(Tray::class)]
class TrayFactory extends Factory
{
    public function definition(): array
    {
        $name = ucfirst(fake()->word());

        // Defaults to living directly on a fresh Project when no trayable
        // is specified. Use forProject()/forShelf()/forWorkbench() to
        // target a specific parent instead.
        return [
            'trayable_type' => Project::class,
            'trayable_id' => Project::factory(),
            'name' => $name,
            'position' => 0,
        ];
    }

    public function forProject(Project $project): static
    {
        return $this->forTrayable($project);
    }

    protected function forTrayable(Model $trayable): static
    {
        return $this->state(
            fn () => [
                'trayable_type' => $trayable::class,
                'trayable_id' => $trayable->id,
            ],
        );
    }
}
