<?php

namespace Database\Factories;

use App\Models\Project;
use App\Models\Tray;
use Illuminate\Database\Eloquent\Attributes\UseModel;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

#[UseModel(Tray::class)]
class TrayFactory extends Factory
{
    // Placeholder token names - swap these for your actual configured
    // Tailwind color tokens (e.g. your primary/accent variables plus
    // whichever preset palette names your component system exposes).
    protected array $colors = [
        'primary',
        'sky',
        'emerald',
        'amber',
        'rose',
        'violet',
        'slate',
        'cyan',
        'orange',
    ];

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
            'slug' => Str::slug($name),
            'color' => fake()->randomElement($this->colors),
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
