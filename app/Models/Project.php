<?php

namespace App\Models;

use App\Concerns\HasPosition;
use App\Enums\ProjectColor;
use App\Observers\ProjectObserver;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use LogicException;

#[Table('projects')]
#[Fillable(['user_id', 'name', 'icon', 'color', 'position'])]
#[ObservedBy([ProjectObserver::class])]
class Project extends Model
{
    use HasFactory;
    use HasPosition;
    use SoftDeletes;

    public function trayForTasks(): Tray
    {
        return $this->unsortedTray ??
            throw new LogicException('Shelf has no unsorted tray');
    }

    protected function casts(): array
    {
        return [
            'position' => 'integer',
            'color' => ProjectColor::class,
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Trays that sit directly under this Project, skipping Shelf.
     */
    public function trays(): MorphMany
    {
        return $this->morphMany(Tray::class, 'trayable');
    }

    /**
     * All tasks belonging to this project, regardless of shelf/tray placement.
     * Denormalized project_id on Task makes this a single indexed query.
     */
    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class);
    }

    public function workbench(): HasOne
    {
        return $this->hasOne(Workbench::class);
    }

    public function workbenchTasks(): HasManyThrough
    {
        return $this->hasManyThrough(WorkbenchTask::class, Workbench::class);
    }

    public function unsortedTray()
    {
        return $this->morphOne(Tray::class, 'trayable')->where(
            'name',
            'Unsorted',
        );
    }
}
