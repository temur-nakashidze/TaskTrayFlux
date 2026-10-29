<?php

use App\Models\Cut;
use App\Models\Project;
use App\Models\Shelf;
use App\Models\Task;
use App\Models\Tray;
use App\Models\User;
use Livewire\Livewire;

test('shelf trash restores its trays and tasks', function () {
    $user = User::factory()->create();
    $project = Project::factory()->create(['user_id' => $user->id]);
    $shelf = $project->shelves()->create(['name' => 'Archived shelf']);
    $tray = Tray::factory()->create([
        'trayable_type' => Shelf::class,
        'trayable_id' => $shelf->id,
    ]);
    $task = Task::factory()->create([
        'user_id' => $user->id,
        'project_id' => $project->id,
        'tray_id' => $tray->id,
    ]);
    $this->actingAs($user);

    $shelf->delete();

    expect($tray->fresh()->trashed())->toBeTrue()
        ->and($task->fresh()->trashed())->toBeTrue();

    Livewire::test('project.shelves-trash')
        ->call('open', $project->id)
        ->assertSee('Archived shelf')
        ->set('selected', [$shelf->id])
        ->call('restore');

    expect($shelf->fresh()->trashed())->toBeFalse()
        ->and($tray->fresh()->trashed())->toBeFalse()
        ->and($task->fresh()->trashed())->toBeFalse();
});

test('permanently deleting a shelf also permanently deletes its trays and tasks', function () {
    $user = User::factory()->create();
    $project = Project::factory()->create(['user_id' => $user->id]);
    $shelf = $project->shelves()->create(['name' => 'Shelf to purge']);
    $tray = Tray::factory()->create([
        'trayable_type' => Shelf::class,
        'trayable_id' => $shelf->id,
    ]);
    $task = Task::factory()->create([
        'user_id' => $user->id,
        'project_id' => $project->id,
        'tray_id' => $tray->id,
    ]);
    $cut = Cut::factory()->for($task)->create();

    $this->actingAs($user);

    $shelf->delete();

    Livewire::test('project.shelves-trash')
        ->call('open', $project->id)
        ->set('selected', [$shelf->id])
        ->call('forceDelete');

    $this->assertModelMissing($shelf);
    $this->assertModelMissing($tray);
    $this->assertModelMissing($task);
    $this->assertModelMissing($cut);
});

test('trays trash only shows trays belonging to the selected owner', function () {
    $user = User::factory()->create();
    $project = Project::factory()->create(['user_id' => $user->id]);
    $shelf = $project->shelves()->create(['name' => 'Shelf']);
    $projectTray = $project->trays()->create(['name' => 'Project trashed tray']);
    $shelfTray = Tray::factory()->create([
        'trayable_type' => Shelf::class,
        'trayable_id' => $shelf->id,
        'name' => 'Shelf trashed tray',
    ]);

    $this->actingAs($user);

    $projectTray->delete();
    $shelfTray->delete();

    Livewire::test('project.trays-trash')
        ->call('open', Project::class, $project->id)
        ->assertSee('Project trashed tray')
        ->assertDontSee('Shelf trashed tray')
        ->set('selected', [$shelfTray->id])
        ->call('forceDelete');

    $this->assertModelExists($shelfTray);

    Livewire::test('project.trays-trash')
        ->call('open', Project::class, $project->id)
        ->assertSee('Project trashed tray')
        ->set('selected', [$projectTray->id])
        ->call('restore');

    expect($projectTray->fresh()->trashed())->toBeFalse();

    Livewire::test('project.trays-trash')
        ->call('open', Shelf::class, $shelf->id)
        ->assertSee('Shelf trashed tray')
        ->assertDontSee('Project trashed tray')
        ->set('selected', [$shelfTray->id])
        ->call('forceDelete');

    $this->assertModelMissing($shelfTray);
});

test('trash modals reject owners from another users project', function () {
    $user = User::factory()->create();
    $otherProject = Project::factory()->create();
    $otherShelf = $otherProject->shelves()->create(['name' => 'Private shelf']);

    $this->actingAs($user);

    Livewire::test('project.shelves-trash')
        ->call('open', $otherProject->id)
        ->assertStatus(404);

    Livewire::test('project.trays-trash')
        ->call('open', Project::class, $otherProject->id)
        ->assertStatus(404);

    Livewire::test('project.trays-trash')
        ->call('open', Shelf::class, $otherShelf->id)
        ->assertStatus(404);
});

test('section archive controls dispatch the correct trash events', function () {
    $user = User::factory()->create();
    $project = Project::factory()->create(['user_id' => $user->id]);
    $shelf = $project->shelves()->create(['name' => 'Current shelf']);

    $this->actingAs($user);

    Livewire::test('project.shelves', ['project' => $project])
        ->call('openTrash')
        ->assertDispatched('open-shelves-trash', fn ($event, $params) => $params['projectId'] === $project->id);

    Livewire::test('project.trays', ['owner' => $project])
        ->call('openTrash')
        ->assertDispatched('open-trays-trash', fn ($event, $params) => $params['ownerType'] === Project::class
            && $params['ownerId'] === $project->id);

    Livewire::test('project.trays', ['owner' => $shelf])
        ->call('openTrash')
        ->assertDispatched('open-trays-trash', fn ($event, $params) => $params['ownerType'] === Shelf::class
            && $params['ownerId'] === $shelf->id);
});

test('tray actions are available from the header context menu', function () {
    $user = User::factory()->create();
    $project = Project::factory()->create(['user_id' => $user->id]);

    $this->actingAs($user);

    Livewire::test('project.trays', ['owner' => $project])
        ->assertSee('x-on:contextmenu.prevent="show($event)"', false)
        ->assertSee('data-ui-context-menu', false)
        ->assertSee('min-w-48 rounded-lg border border-zinc-200 bg-white', false)
        ->assertSee('text-zinc-700 hover:bg-zinc-50 dark:text-white', false)
        ->assertSee('opacity-90 transition-opacity group-hover/menu-item:opacity-100', false)
        ->assertSee('Open Trash')
        ->assertSee('Delete')
        ->assertDontSee('ellipsis-horizontal');
});

test('empty sections keep trash reachable when deleted containers exist', function () {
    $user = User::factory()->create();
    $project = Project::factory()->create(['user_id' => $user->id]);
    $shelf = $project->shelves()->create(['name' => 'Deleted shelf']);
    $tray = $project->trays()->firstOrFail();

    $this->actingAs($user);

    $shelf->delete();
    $tray->delete();

    Livewire::test('project.shelves', ['project' => $project])
        ->assertSee('No shelves yet')
        ->assertSee('Open trash')
        ->call('openTrash')
        ->assertDispatched('open-shelves-trash');

    Livewire::test('project.trays', ['owner' => $project])
        ->assertSee('No trays yet')
        ->assertSee('Open trash')
        ->call('openTrash')
        ->assertDispatched('open-trays-trash');
});
