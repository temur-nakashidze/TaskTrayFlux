<?php

use App\Concerns\Livewire\Tasks\AddsRoutedTasks;
use App\Enums\ProjectColor;
use App\Enums\TagColor;
use App\Models\Task;
use Livewire\Attributes\Computed;
use Livewire\Component;

new class extends Component
{
    use AddsRoutedTasks;

    #[Computed]
    public function routes(): array
    {
        return auth()->user()->projects()
            ->with([
                'trays' => fn ($q) => $q->ordered('asc'),
                'shelves' => fn ($q) => $q->ordered('asc')->with(['trays' => fn ($q) => $q->ordered('asc')]),
            ])
            ->get()
            ->map(fn ($p) => [
                'id' => $p->id,
                'name' => $p->name,
                'tint' => ($p->color ?? ProjectColor::Cyan)->tint(),
                'trays' => $p->trays->map->only('id', 'name')->values(),
                'shelves' => $p->shelves->map(fn ($s) => [
                    'id' => $s->id,
                    'name' => $s->name,
                    'trays' => $s->trays->map->only('id', 'name')->values(),
                ])->values(),
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
