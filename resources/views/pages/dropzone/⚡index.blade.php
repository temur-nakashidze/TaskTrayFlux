<?php

use App\Enums\ProjectColor;
use App\Enums\TagColor;
use App\Models\Task;
use Livewire\Attributes\Computed;
use Livewire\Component;

new class extends Component
{
    #[Computed]
    public function routes(): array
    {
        return auth()->user()->projects()
            ->with(['trays' => fn ($q) => $q->ordered('asc')])
            ->get()
            ->map(fn ($p) => [
                'id' => $p->id,
                'name' => $p->name,
                'tint' => ($p->color ?? ProjectColor::Cyan)->tint(),
                'trays' => $p->trays->map->only('id', 'name')->values(),
            ])
            ->values()
            ->all();
    }

    #[Computed]
    public function tagOptions(): array
    {
        return auth()->user()->tags()->orderBy('name')->get(['id', 'name', 'color'])
            ->map(fn ($t) => [
                'id' => $t->id,
                'name' => $t->name,
                'tint' => ($t->color ?? TagColor::Default)->tint(),
                'chip' => ($t->color ?? TagColor::Default)->chip(),
            ])
            ->all();
    }

    public function addRoutedTask(string $title, ?int $projectId, ?string $kind, ?int $destId): void
    {
        $title = mb_substr(trim($title), 0, 255);

        if ($title === '') {
            return;
        }

        if ($projectId === null) {
            // no route: your existing inbox behavior
            return;
        }

        $project = auth()->user()->projects()->findOrFail($projectId);

        // if ($kind === 'shelf') { ... } // later

        $tray = $kind === 'tray' ? $project->trays()->findOrFail($destId) : $project->trays()->ordered('asc')->firstOrFail();

        $tray->tasks()->create([
            'title' => $title,
            'user_id' => auth()->id(),
            'project_id' => $project->id,
        ]);

        $this->dispatch('tasks-moved');
    }

    private function attachTags(Task $task, array $tags): void
    {
        $user = auth()->user();

        // existing tags: re-checked against the user's own tags
        $ids = $user->tags()->whereKey(collect($tags)->pluck('id')->filter()->all())->pluck('id');

        // new tags, or tags picked from the local list after being created
        foreach (collect($tags)->whereNull('id')->pluck('name') as $name) {
            $name = mb_substr(trim($name), 0, 50);

            if ($name !== '') {
                $ids->push($user->tags()->firstOrCreate(['name' => $name])->id);
            }
        }

        $task->tags()->syncWithoutDetaching($ids->unique()->all());
    }

    public function render()
    {
        return $this->view()->title('Drop Zone');
    }
};
?>

<div>
    <div class="grid grid-cols-2 gap-8">

        @include('pages.dropzone.drop-area')
    </div>
</div>
