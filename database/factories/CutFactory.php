<?php

namespace Database\Factories;

use App\Models\Cut;
use App\Models\Task;
use Illuminate\Database\Eloquent\Factories\Attributes\UseModel;
use Illuminate\Database\Eloquent\Factories\Factory;

#[UseModel(Cut::class)]
class CutFactory extends Factory
{
    public function definition(): array
    {
        return [
            'task_id' => Task::factory(),
            'title' => ucfirst(fake()->sentence(3)),
            'position' => 0,
        ];
    }

    public function completed(): static
    {
        return $this->state(fn () => ['completed' => true]);
    }
}
