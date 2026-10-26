<?php

namespace App\Models;

use App\Concerns\HasPosition;
use App\Observers\TrayObserver;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Table('trays')]
#[Fillable(['trayable_type', 'trayable_id', 'name', 'color', 'position'])]
#[ObservedBy([TrayObserver::class])]
class Tray extends Model
{
    use HasFactory;
    use HasPosition;
    use SoftDeletes;

    public function trayForTasks(): Tray
    {
        return $this;
    }

    public function positionGroupColumns(): array
    {
        return ['trayable_type', 'trayable_id'];
    }

    protected function casts(): array
    {
        return [
            'position' => 'integer',
        ];
    }

    /**
     * The Project or Shelf this Tray belongs to.
     */
    public function trayable(): MorphTo
    {
        return $this->morphTo();
    }

    /** Tasks placed in this Tray. Only populated when the Tray belongs to a Project. */
    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class);
    }

    public function completedTasks(): HasMany
    {
        return $this->hasMany(Task::class)->whereNotNull('completed_at');
    }

    public function activeTasks(): HasMany
    {
        return $this->hasMany(Task::class)->whereNull('completed_at');
    }

    /**
     * Workbench-pull references placed in this Tray. Only populated when
     * the Tray belongs to a Workbench.
     */
    public function workbenchTasks(): HasMany
    {
        return $this->hasMany(WorkbenchTask::class)->orderBy('position');
    }

    #[Scope]
    protected function forUser(Builder $query, User $user): void
    {
        $query->where(function (Builder $query) use ($user) {
            $query
                ->whereHasMorph(
                    'trayable',
                    Project::class,
                    fn (Builder $query) => $query->where('user_id', $user->id),
                )
                ->orWhereHasMorph(
                    'trayable',
                    Shelf::class,
                    fn (Builder $query) => $query->whereHas(
                        'project',
                        fn (Builder $query) => $query->where('user_id', $user->id),
                    ),
                );
        });
    }
}
