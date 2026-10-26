<?php

namespace App\Models;

use App\Concerns\HasPosition;
use App\Contracts\HasTrays;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable(['name'])]
class Shelf extends Model implements HasTrays
{
    use HasPosition;
    use SoftDeletes;

    public function positionGroupColumns(): array
    {
        return ['project_id'];
    }

    public function owningProject(): Project
    {
        return $this->project;
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function trays(): MorphMany
    {
        return $this->morphMany(Tray::class, 'trayable');
    }

    // Shelf model
    public function tasks(): HasManyThrough
    {
        return $this->hasManyThrough(Task::class, Tray::class, 'trayable_id', 'tray_id')
            ->where('trays.trayable_type', $this->getMorphClass());
    }

    protected function progress(): Attribute
    {
        return Attribute::get(function () {
            $total = (int) ($this->tasks_count ?? 0);

            return $total === 0
                ? 0
                : (int) round(($this->completed_tasks_count ?? 0) / $total * 100);
        });
    }
}
