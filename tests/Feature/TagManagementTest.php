<?php

use App\Enums\TagColor;
use App\Models\Project;
use App\Models\Tag;
use App\Models\Task;
use App\Models\Tray;
use App\Models\User;
use Livewire\Livewire;

test('the sidebar tag manager lists only the signed-in users tags', function () {
    $user = User::factory()->create();
    $tag = Tag::factory()->for($user)->create(['name' => 'Personal']);
    $otherTag = Tag::factory()->create(['name' => 'Private']);

    $this->actingAs($user);

    Livewire::test('sidebar.tags')
        ->assertSee('Tags')
        ->assertSee('Personal')
        ->assertDontSee('Private')
        ->assertSee('aria-label="Tags"', false)
        ->assertSee('compact-sidebar-tag-'.$tag->id, false)
        ->assertSee('Collapse tags')
        ->assertSee('x-on:click="expanded = ! expanded"', false)
        ->assertSee('x-show="expanded"', false)
        ->assertDontSee('wire:click="toggleExpanded"', false)
        ->assertSee('Rename or recolor')
        ->call('startAdding')
        ->assertSee('New tag name');
});

test('users can create a tag from the sidebar', function () {
    $user = User::factory()->create();

    $this->actingAs($user);

    Livewire::test('sidebar.tags')
        ->call('startAdding')
        ->assertSee('Default')
        ->assertSee('Rose')
        ->set('newTagName', '  Planning  ')
        ->set('newTagColor', TagColor::Rose->value)
        ->call('createTag')
        ->assertSee('Planning')
        ->assertDispatched('tags-updated');

    $tag = $user->tags()->where('name', 'Planning')->firstOrFail();

    expect($tag->color)->toBe(TagColor::Rose);
});

test('users cannot create a duplicate tag name', function () {
    $user = User::factory()->create();
    Tag::factory()->for($user)->create(['name' => 'Planning']);

    $this->actingAs($user);

    Livewire::test('sidebar.tags')
        ->set('newTagName', 'Planning')
        ->call('createTag')
        ->assertHasErrors(['newTagName' => 'unique']);

    expect($user->tags()->where('name', 'Planning')->count())->toBe(1);
});

test('users can rename and recolor their tags', function () {
    $user = User::factory()->create();
    $tag = Tag::factory()->for($user)->create(['name' => 'Old name']);

    $this->actingAs($user);

    Livewire::test('sidebar.tags')
        ->call('editTag', $tag->id)
        ->set('tagName', 'New name')
        ->set('tagColor', TagColor::Rose->value)
        ->call('updateTag')
        ->assertDispatched('tags-updated');

    expect($tag->fresh()->name)->toBe('New name')
        ->and($tag->fresh()->color)->toBe(TagColor::Rose);
});

test('users cannot rename a tag to a name already in use', function () {
    $user = User::factory()->create();
    $tag = Tag::factory()->for($user)->create(['name' => 'First']);
    Tag::factory()->for($user)->create(['name' => 'Taken']);

    $this->actingAs($user);

    Livewire::test('sidebar.tags')
        ->call('editTag', $tag->id)
        ->set('tagName', 'Taken')
        ->call('updateTag')
        ->assertHasErrors(['tagName' => 'unique']);

    expect($tag->fresh()->name)->toBe('First');
});

test('deleting a tag removes it from the task and soft deletes the tag', function () {
    $user = User::factory()->create();
    $tag = Tag::factory()->for($user)->create();
    $task = Task::factory()->for($user)->create();
    $task->tags()->attach($tag);

    $this->actingAs($user);

    Livewire::test('sidebar.tags')
        ->call('confirmDelete', $tag->id)
        ->call('deleteTag')
        ->assertDispatched('tags-updated');

    $this->assertSoftDeleted($tag);
    $this->assertDatabaseMissing('task_tag', [
        'task_id' => $task->id,
        'tag_id' => $tag->id,
    ]);
});

test('tag actions cannot access another users tags', function () {
    $user = User::factory()->create();
    $otherTag = Tag::factory()->create();

    $this->actingAs($user);

    Livewire::test('sidebar.tags')
        ->call('editTag', $otherTag->id)
        ->assertNotFound();

    Livewire::test('sidebar.tags')
        ->call('confirmDelete', $otherTag->id)
        ->assertNotFound();
});

test('task entry restores a deleted tag when creating a new task with it', function () {
    $user = User::factory()->create();
    $project = Project::factory()->create(['user_id' => $user->id]);
    $tray = Tray::factory()->forProject($project)->create();
    $tag = Tag::factory()->for($user)->create(['name' => 'Reused tag']);
    $tag->delete();

    $this->actingAs($user);

    Livewire::test('project.trays', ['owner' => $project])
        ->call('addTask', $tray->id, 'A tagged task', [['id' => null, 'name' => 'Reused tag']])
        ->assertDispatched('tags-updated');

    $task = Task::query()->where('title', 'A tagged task')->firstOrFail();

    expect($tag->fresh()->trashed())->toBeFalse()
        ->and($task->tags()->whereKey($tag->id)->exists())->toBeTrue();
});
