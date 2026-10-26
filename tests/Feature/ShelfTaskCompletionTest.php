<?php

use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Livewire\Livewire;

test('completing a shelf task updates the task rather than its tray', function () {
    $user = User::factory()->create();
    $project = Project::factory()->create(['user_id' => $user->id]);

    $unrelatedTask = Task::factory()->create([
        'user_id' => $user->id,
        'project_id' => $project->id,
    ]);
    $secondUnrelatedTask = Task::factory()->create([
        'user_id' => $user->id,
        'project_id' => $project->id,
    ]);

    $shelf = $project->shelves()->create(['name' => 'Shelf']);
    $tray = $shelf->trays()->create(['name' => 'Tray']);
    $task = Task::factory()->create([
        'user_id' => $user->id,
        'project_id' => $project->id,
        'tray_id' => $tray->id,
    ]);

    expect($task->id)->not->toBe($tray->id);

    $this->actingAs($user);

    Livewire::test('project.trays', ['owner' => $shelf])
        ->call('completeTask', $task->id)
        ->assertHasNoErrors();

    expect($task->fresh()->completed_at)->not->toBeNull();
    expect($unrelatedTask->fresh()->completed_at)->toBeNull();
    expect($secondUnrelatedTask->fresh()->completed_at)->toBeNull();
});
