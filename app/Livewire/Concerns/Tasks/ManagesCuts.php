<?php

namespace App\Livewire\Concerns\Tasks;

use App\Actions\Task\CompleteCut;
use App\Actions\Task\RestoreCut;
use App\Models\Cut;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\Renderless;

trait ManagesCuts
{
    /** Cuts that belong to tasks this component may touch. */
    protected function cutScope(): Builder
    {
        return Cut::query()->whereIn('task_id', $this->taskScope()->select('id'));
    }

    public function addCut(int $taskId): void
    {
        $this->taskScope()->findOrFail($taskId)->cuts()->create();
    }

    public function completeCut(int $cutId): void
    {
        app(CompleteCut::class)->handle($this->cutScope()->findOrFail($cutId));
    }

    public function restoreCut(int $cutId): void
    {
        app(RestoreCut::class)->handle($this->cutScope()->findOrFail($cutId));
    }

    #[Renderless]
    public function renameCut(int $cutId, string $title): void
    {
        $title = mb_substr(trim($title), 0, 255);

        if ($title !== '') {
            $this->cutScope()->findOrFail($cutId)->update(['title' => $title]);
        }
    }

    #[Renderless]
    public function moveCut(int $cutId, int $position): void
    {
        $this->cutScope()->findOrFail($cutId)->moveToPosition($position, 'asc');
    }
}
