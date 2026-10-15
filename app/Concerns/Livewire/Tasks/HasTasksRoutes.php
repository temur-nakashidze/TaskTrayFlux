<?php

namespace App\Concerns\Livewire\Tasks;

use App\Enums\ProjectColor;
use Livewire\Attributes\Computed;

trait HasTasksRoutes
{
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
                'tint' => ($p->color ?? ProjectColor::Neutral)->tint(),
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
}
