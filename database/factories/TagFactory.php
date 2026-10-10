<?php

namespace Database\Factories;

use App\Models\Tag;
use App\Models\User;
use Illuminate\Database\Eloquent\Attributes\UseModel;
use Illuminate\Database\Eloquent\Factories\Factory;

#[UseModel(Tag::class)]
class TagFactory extends Factory
{
    // Placeholder token names - swap these for your actual configured
    // Tailwind color tokens (e.g. your primary/accent variables plus
    // whichever preset palette names your component system exposes).
    protected array $colors = [
        'primary', 'sky', 'emerald', 'amber',
        'rose', 'violet', 'slate', 'cyan', 'orange',
    ];

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'name' => fake()->unique()->word(),
            'color' => fake()->randomElement($this->colors),
        ];
    }
}
