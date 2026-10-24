<?php

namespace Database\Seeders;

use App\Models\Cut;
use App\Models\Project;
use App\Models\Tag;
use App\Models\Task;
use App\Models\Tray;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $user =
            User::first() ??
            User::factory()->create([
                'name' => 'Temo',
                'email' => 'temo@example.test',
            ]);

        $tags = collect([
            'urgent',
            'bug',
            'idea',
            'investigate',
            'quick-win',
        ])->map(
            fn ($name) => Tag::factory()->create([
                'user_id' => $user->id,
                'name' => $name,
            ]),
        );

        // --- Project 1: trays straight on the project, a few tasks (one with
        // Cuts, some completed), and a Workbench with pulled-in tasks ---
        $project1 = Project::factory()->create([
            'user_id' => $user->id,
            'position' => 0,
        ]);

        [$todo, $later, $ideas] = $this->trays($project1, [
            'Todo',
            'Later',
            'Ideas',
        ]);

        $todoTasks = $this->tasks($todo, $user, 4);
        $this->tasks($later, $user, 2);
        $this->tasks($ideas, $user, 3);
        $this->tasks($project1->unsortedTray, $user, 3);

        // A task with Cuts - one finished, two open, to exercise the
        // "can't complete while Cuts are open" rule in the UI.
        $taskWithCuts = Task::factory()
            ->forTray($todo)
            ->create([
                'user_id' => $user->id,
                'title' => 'Ship the auth flow',
                'position' => 10,
            ]);
        Cut::factory()->create([
            'task_id' => $taskWithCuts->id,
            'title' => 'Add login form',
            'position' => 0,
        ]);
        Cut::factory()->create([
            'task_id' => $taskWithCuts->id,
            'title' => 'Add password reset',
            'position' => 1,
        ]);
        Cut::factory()->create([
            'task_id' => $taskWithCuts->id,
            'title' => 'Write tests',
            'position' => 2,
        ]);
        $taskWithCuts->tags()->attach($tags->random(2));

        // Tag a handful of other tasks too.
        $todoTasks->each(
            fn ($t) => $t->tags()->attach($tags->random(rand(0, 2))),
        );

        // Pull a few tasks into this project's Workbench. References are
        // appended to its single ordered list, the real tasks stay in their trays.
        $workbench = $project1->workbench;
        $workbench->pull($taskWithCuts);
        $workbench->pull($todoTasks->first());
        $workbench->pull($todoTasks->last());

        // Needs the blocked_at migration:
        // $todoTasks->first()->forceFill(["blocked_at" => now()])->save();

        // --- Project 2: a small second project, to exercise the sidebar order ---
        $project2 = Project::factory()->create([
            'user_id' => $user->id,
            'position' => 1,
        ]);

        [$todo2, $ideas2] = $this->trays($project2, ['Todo', 'Ideas']);
        $this->tasks($todo2, $user, 3);
        $this->tasks($ideas2, $user, 2);
    }

    /**
     * Creates the named trays in order, then keeps the project's Unsorted tray last
     * (the observer creates it, or we create it here if it does not exist yet).
     *
     * @return array<int, Tray>
     */
    private function trays(Project $project, array $names): array
    {
        $trays = collect($names)
            ->map(
                fn ($name, $i) => Tray::factory()
                    ->forProject($project)
                    ->create([
                        'name' => $name,
                        'position' => $i,
                    ]),
            )
            ->all();

        $unsorted =
            $project->unsortedTray ??
            Tray::factory()
                ->forProject($project)
                ->create([
                    'name' => 'Unsorted',
                ]);

        $unsorted->moveToPosition(PHP_INT_MAX);

        return $trays;
    }

    /** Tasks in a tray with positions 0..n-1. */
    private function tasks(Tray $tray, User $user, int $count): Collection
    {
        $factory = Task::factory($count)
            ->forTray($tray)
            ->sequence(fn ($sequence) => ['position' => $sequence->index]);

        return $factory->create([
            'user_id' => $user->id,
        ]);
    }
}
