<?php

use App\Models\Project;
use App\Models\User;
use Livewire\Livewire;

test('guests are redirected to the login page', function () {
    $response = $this->get(route('dashboard'));
    $response->assertRedirect(route('login'));
});

test('authenticated users can visit the dashboard', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $response = $this->get(route('dashboard'));
    $response->assertOk()
        ->assertSee('Tags')
        ->assertSee('No tags yet')
        ->assertSee('Add tag')
        ->assertSee('transition-[width] duration-300', false)
        ->assertSee('overflow-x-clip', false)
        ->assertSee('in-data-flux-sidebar-collapsed-desktop:-mx-2', false)
        ->assertSee('sidebar-no-resize', false)
        ->assertSee('sidebar-collapse-toggle', false)
        ->assertSee('in-data-flux-sidebar-collapsed-desktop:group-hover/sidebar-logo:opacity-0', false)
        ->assertSee('in-data-flux-sidebar-collapsed-desktop:group-hover/sidebar-logo:pointer-events-none', false);
});

test('sidebar projects show compact initials and keep sort handles', function () {
    $user = User::factory()->create();
    $twoWordProject = Project::factory()->for($user)->create(['name' => 'Design Studio', 'color' => 'violet']);
    $twoWordProject->shelves()->create(['name' => 'Research']);
    Project::factory()->for($user)->create(['name' => 'Personal']);

    $this->actingAs($user);

    Livewire::test('sidebar.projects-list')
        ->assertSee('DS')
        ->assertSee('Pe')
        ->assertSee('aria-label="Design Studio"', false)
        ->assertSee('wire:sort:handle', false)
        ->assertSee('in-data-flux-sidebar-collapsed-desktop:hidden', false)
        ->assertSee('size-8', false)
        ->assertSee('bg-violet-500/20!', false)
        ->assertSee('text-violet-700 dark:text-violet-300', false);
});
