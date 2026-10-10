<?php

namespace Database\Factories;

use App\Models\Project;
use Illuminate\Database\Eloquent\Attributes\UseModel;
use Illuminate\Database\Eloquent\Factories\Factory;

#[UseModel(Project::class)]
class ProjectFactory extends Factory
{
    /**
     * A small curated set of Phosphor icon names, since real projects
     * would be picked from an icon picker, not fully random.
     */
    protected array $icons = [
        'rocket', 'code', 'terminal', 'cube', 'flask',
        'gear', 'lightning', 'compass', 'stack', 'puzzle-piece',
    ];

    // Placeholder token names - swap these for your actual configured
    // Tailwind color tokens (e.g. your primary/accent variables plus
    // whichever preset palette names your component system exposes).
    protected array $colors = [
        'primary', 'accent', 'sky', 'emerald', 'amber',
        'rose', 'violet', 'slate', 'cyan', 'orange',
    ];

    public function definition(): array
    {
        return [
            'user_id' => \App\Models\User::factory(),
            'name' => ucfirst(fake()->words(2, true)),
            'icon' => fake()->randomElement($this->icons),
            'color' => fake()->randomElement($this->colors),
            'position' => 0,
        ];
    }
}
