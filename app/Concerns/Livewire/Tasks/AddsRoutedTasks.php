<?php

namespace App\Concerns\Livewire\Tasks;

use App\Actions\Task\CreateTask;
use Livewire\Attributes\On;

trait AddsRoutedTasks
{
    #[On('add-routed-task')]
    public function addRoutedTask(
        string $title,
        ?int $projectId = null,
        ?int $shelfId = null,
        ?int $trayId = null,
        array $tags = [],
    ): void {
        $user = auth()->user();

        abort_if($shelfId && ! $projectId, 422);

        $project = $projectId ? $user->projects()->findOrFail($projectId) : null;

        // the owner is the shelf when one was chosen, otherwise the project
        $owner = $shelfId ? $project->shelves()->findOrFail($shelfId) : $project;

        $tray = match (true) {
            $owner === null => null,
            $trayId !== null => $owner->trays()->findOrFail($trayId),
            default => $owner->trays()->ordered('asc')->first(),
        };

        app(CreateTask::class)->handle($user, $title, tray: $tray, project: $project, tags: $tags);

        $this->dispatch('tasks-moved');
        $this->dispatch('tags-updated');
    }
}
