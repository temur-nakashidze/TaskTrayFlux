<?php

use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Livewire\Livewire;

test('users cannot visit another users project pages', function () {
    $user = User::factory()->create();
    $owner = User::factory()->create();
    $ownProject = Project::factory()->create(['user_id' => $user->id]);
    $ownShelf = $ownProject->shelves()->create(['name' => 'My shelf']);
    $project = Project::factory()->create(['user_id' => $owner->id]);
    $shelf = $project->shelves()->create(['name' => 'Private shelf']);

    $this->actingAs($user);

    $this->get(route('project', $ownProject))->assertOk();
    $this->get(route('workbench', $ownProject))->assertOk();
    $this->get(route('blueprint', $ownProject))->assertOk();
    $this->get(route('shelf', ['project' => $ownProject, 'shelf' => $ownShelf]))->assertOk();

    $this->get(route('project', $project))->assertNotFound();
    $this->get(route('workbench', $project))->assertNotFound();
    $this->get(route('blueprint', $project))->assertNotFound();
    $this->get(route('shelf', ['project' => $project, 'shelf' => $shelf]))->assertNotFound();
});

test('project header cannot rename another users project', function () {
    $user = User::factory()->create();
    $ownProject = Project::factory()->create(['user_id' => $user->id]);
    $otherProject = Project::factory()->create(['name' => 'Private project']);

    $this->actingAs($user);

    Livewire::test('project.project-header', ['project' => $ownProject])
        ->call('renameProject', $otherProject->id, 'Changed by another user')
        ->assertStatus(404);

    expect($otherProject->fresh()->name)->toBe('Private project');
});

test('task board actions cannot update another users task', function () {
    $user = User::factory()->create();
    $ownProject = Project::factory()->create(['user_id' => $user->id]);
    $otherProject = Project::factory()->create();
    $otherTask = Task::factory()->create([
        'user_id' => $otherProject->user_id,
        'project_id' => $otherProject->id,
        'note' => 'Private note',
    ]);

    $this->actingAs($user);

    Livewire::test('project.trays', ['owner' => $ownProject])
        ->call('redactingNote', $otherTask)
        ->assertStatus(404);

    Livewire::test('project.trays', ['owner' => $ownProject])
        ->set('selectedTask', $otherTask->id)
        ->set('taskNote', 'Changed by another user')
        ->call('updateNote')
        ->assertStatus(404);

    expect($otherTask->fresh()->note)->toBe('Private note');
});

test('sidebar actions cannot move another users project or tray task', function () {
    $user = User::factory()->create();
    $ownProject = Project::factory()->create(['user_id' => $user->id]);
    $otherProject = Project::factory()->create();
    $otherTask = Task::factory()->create([
        'user_id' => $otherProject->user_id,
        'project_id' => $otherProject->id,
        'tray_id' => $otherProject->trays()->firstOrFail()->id,
    ]);
    $otherTray = $otherProject->trays()->firstOrFail();

    $this->actingAs($user);

    Livewire::test('sidebar.projects-list')
        ->call('moveProject', $otherProject->id, 0)
        ->assertStatus(404);

    Livewire::test('sidebar.projects-list')
        ->call('moveTask', $otherTask->id, 0, $otherTray->id)
        ->assertStatus(404);

    expect($otherTask->fresh()->tray_id)->toBe($otherTray->id);
});

test('users cannot open another users tray trash', function () {
    $user = User::factory()->create();
    $otherProject = Project::factory()->create();
    $otherTray = $otherProject->trays()->firstOrFail();
    $trashedTask = Task::factory()->create([
        'user_id' => $otherProject->user_id,
        'project_id' => $otherProject->id,
        'tray_id' => $otherTray->id,
    ]);
    $trashedTask->delete();

    $this->actingAs($user);

    Livewire::test('project.tray-trash')
        ->call('open', $otherTray->id)
        ->assertStatus(404);

    expect($trashedTask->fresh()->trashed())->toBeTrue();
});

test('reordering a users projects and shelves does not change another users positions', function () {
    $user = User::factory()->create();
    $ownProject = Project::factory()->create(['user_id' => $user->id, 'position' => 0]);
    Project::factory()->create(['user_id' => $user->id, 'position' => 1]);
    $otherProject = Project::factory()->create(['position' => 10]);

    $ownShelf = $ownProject->shelves()->create(['name' => 'First']);
    $ownShelf->position = 0;
    $ownShelf->save();
    $secondOwnShelf = $ownProject->shelves()->create(['name' => 'Second']);
    $secondOwnShelf->position = 1;
    $secondOwnShelf->save();
    $otherShelf = $otherProject->shelves()->create(['name' => 'Other']);
    $otherShelf->position = 10;
    $otherShelf->save();

    $this->actingAs($user);

    Livewire::test('sidebar.projects-list')
        ->call('moveProject', $ownProject->id, 1)
        ->call('moveShelf', $ownShelf->id, 1);

    expect($otherProject->fresh()->position)->toBe(10);
    expect($otherShelf->fresh()->position)->toBe(10);
});

test('a task cannot be pulled onto another projects workbench', function () {
    $user = User::factory()->create();
    $ownProject = Project::factory()->create(['user_id' => $user->id]);
    $otherProject = Project::factory()->create();
    $task = Task::factory()->create([
        'user_id' => $user->id,
        'project_id' => $ownProject->id,
        'tray_id' => $ownProject->trays()->firstOrFail()->id,
    ]);

    $this->actingAs($user);

    Livewire::test('project.trays', ['owner' => $ownProject])
        ->call('pullToWorkbench', $task->id, $otherProject->workbench->id)
        ->assertStatus(404);

    expect($ownProject->workbench->workbenchTasks()->where('task_id', $task->id)->exists())->toBeFalse();
});

test('shelf pages identify the shelf and provide navigation back to its project', function () {
    $user = User::factory()->create();
    $project = Project::factory()->create(['user_id' => $user->id]);
    $shelf = $project->shelves()->create(['name' => 'In progress']);

    $this->actingAs($user)
        ->get(route('shelf', ['project' => $project, 'shelf' => $shelf]))
        ->assertOk()
        ->assertSee('Back to project')
        ->assertSee('In progress')
        ->assertSee('href="'.route('project', $project).'"', false)
        ->assertSee('href="'.route('workbench', $project).'"', false)
        ->assertSee('href="'.route('blueprint', $project).'"', false);
});

test('sidebar keeps inactive project tools navigable and disables the active tool', function () {
    $user = User::factory()->create();
    $project = Project::factory()->create(['user_id' => $user->id]);

    $this->actingAs($user);

    $assertSidebarTools = function (string $page, string $disabledRoute, string $enabledRoute) use ($project): void {
        $response = $this->get($page);
        $response->assertOk();

        $document = new DOMDocument;
        $previousErrorHandling = libxml_use_internal_errors(true);
        $document->loadHTML($response->getContent());
        libxml_clear_errors();
        libxml_use_internal_errors($previousErrorHandling);

        $xpath = new DOMXPath($document);
        $branch = $xpath->query('//*[@*[name()="wire:key"]="item-'.$project->id.'"]')->item(0);

        expect($branch)->not->toBeNull();
        expect($xpath->query('.//a[@href="'.route($disabledRoute, $project).'"]', $branch)->length)->toBe(0);
        expect($xpath->query('.//a[@href="'.route($enabledRoute, $project).'"]', $branch)->length)->toBe(1);
        expect($xpath->query('.//button[@disabled]', $branch)->length)->toBe(1);

        $label = $xpath->query('.//div[contains(@class, "cursor-default")]', $branch)->item(0);

        expect($label)->not->toBeNull();
        expect($label->hasAttribute('x-on:click'))->toBeFalse();
    };

    $assertSidebarTools(
        route('workbench', $project),
        'workbench',
        'blueprint',
    );
    $assertSidebarTools(
        route('blueprint', $project),
        'blueprint',
        'workbench',
    );
});
