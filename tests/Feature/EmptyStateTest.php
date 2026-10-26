<?php

use App\Models\Project;
use App\Models\Task;
use App\Models\Tray;
use App\Models\User;
use Illuminate\Support\Facades\Blade;
use Livewire\Livewire;

test('the shared empty state renders its message and optional action', function () {
    $withAction = Blade::render(
        '<x-ui.empty-state heading="Nothing here yet" description="Add something to get started."><x-slot:action><button>Take action</button></x-slot:action></x-ui.empty-state>',
    );
    $withoutAction = Blade::render(
        '<x-ui.empty-state heading="Nothing here yet" description="Add something to get started." />',
    );

    expect($withAction)->toContain('Nothing here yet');
    expect($withAction)->toContain('Add something to get started.');
    expect($withAction)->toContain('Take action');
    expect($withAction)->toContain('size-10');
    expect($withoutAction)->not->toContain('Take action');
});

test('empty project boards offer actions to create a tray and a shelf', function () {
    $user = User::factory()->create();
    $project = Project::factory()->create(['user_id' => $user->id]);

    $project->trays()->delete();

    $this->actingAs($user);

    Livewire::test('project.trays', ['owner' => $project])
        ->assertSee('No trays yet')
        ->assertSee('Add first tray')
        ->call('addTray')
        ->assertDontSee('No trays yet');

    Livewire::test('project.shelves', ['project' => $project])
        ->assertSee('No shelves yet')
        ->assertSee('Add first shelf')
        ->call('addShelf')
        ->assertDontSee('No shelves yet');
});

test('the sidebar explains how to create the first project', function () {
    $user = User::factory()->create();

    $this->actingAs($user);

    Livewire::test('sidebar.projects-list')
        ->assertSee('No projects yet')
        ->assertSee('Create project');

    Project::factory()->create(['user_id' => $user->id]);

    Livewire::test('sidebar.projects-list')
        ->assertDontSee('No projects yet');
});

test('the Dropzone empty state only reflects the signed-in users tasks', function () {
    $user = User::factory()->create();
    $anotherUser = User::factory()->create();

    $this->actingAs($user);

    Task::factory()->create(['user_id' => $anotherUser->id]);

    Livewire::test('pages::dropzone.index')
        ->assertSee('No tasks in the Dropzone');

    Task::factory()->create(['user_id' => $user->id]);

    Livewire::test('pages::dropzone.index')
        ->assertDontSee('No tasks in the Dropzone');
});

test('each Workbench lane explains when it is empty', function () {
    $user = User::factory()->create();
    $project = Project::factory()->create(['user_id' => $user->id]);

    $this->actingAs($user)
        ->get(route('workbench', $project))
        ->assertOk()
        ->assertSee('Nothing to do yet')
        ->assertSee('Nothing started yet')
        ->assertSee('No completed tasks yet');
});

test('Workbench lanes hide empty messages when they contain tasks', function () {
    $user = User::factory()->create();
    $project = Project::factory()->create(['user_id' => $user->id]);
    $tray = Tray::factory()->forProject($project)->create();

    $todoTask = Task::factory()->forTray($tray)->create([
        'user_id' => $user->id,
        'title' => 'Prepare outline',
    ]);
    $startedTask = Task::factory()->forTray($tray)->create([
        'user_id' => $user->id,
        'title' => 'Draft introduction',
    ]);
    $startedTask->timeEntries()->create([
        'user_id' => $user->id,
        'started_at' => now()->subMinute(),
        'ended_at' => now(),
    ]);
    $completedTask = Task::factory()->forTray($tray)->create([
        'user_id' => $user->id,
        'title' => 'Choose a topic',
        'completed_at' => now(),
    ]);

    $project->workbench->pull($todoTask);
    $project->workbench->pull($startedTask);
    $project->workbench->pull($completedTask);

    $this->actingAs($user)
        ->get(route('workbench', $project))
        ->assertOk()
        ->assertSee('Prepare outline')
        ->assertSee('Draft introduction')
        ->assertSee('Choose a topic')
        ->assertDontSee('Nothing to do yet')
        ->assertDontSee('Nothing started yet')
        ->assertDontSee('No completed tasks yet');
});

test('tray trash explains when there are no deleted tasks', function () {
    $user = User::factory()->create();
    $project = Project::factory()->create(['user_id' => $user->id]);
    $tray = Tray::factory()->forProject($project)->create();

    $this->actingAs($user);

    Livewire::test('project.tray-trash')
        ->call('open', $tray->id)
        ->assertSee('No deleted tasks');

    $task = Task::factory()->forTray($tray)->create(['user_id' => $user->id]);
    $task->delete();

    Livewire::test('project.tray-trash')
        ->call('open', $tray->id)
        ->assertSee($task->title)
        ->assertDontSee('No deleted tasks');
});
