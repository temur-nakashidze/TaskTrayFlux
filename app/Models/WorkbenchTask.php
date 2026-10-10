<?php

namespace App\Models;

use App\Concerns\HasPosition;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Table('workbench_task')]
#[
    Fillable([
        'task_id',
        'tray_id',
        'position',
    ]),
]
class WorkbenchTask extends Model
{
    use HasPosition;

    protected function casts(): array
    {
        return [
            'position' => 'integer',
        ];
    }

    public function positionGroupColumns(): array
    {
        return ['workbench_id'];
    }

    // Opposite of Task: a reference may ONLY live in a tray owned by its own workbench.

    public function workbench(): BelongsTo
    {
        return $this->belongsTo(Workbench::class);
    }

    /**
     * The real Task this reference points to. Deleting this reference
     * never touches the underlying task's real project/shelf/tray position.
     */
    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class);
    }

    /**
     * The Workbench-owned Tray this reference sits in. Null means it sits
     * in the Workbench's own unsectioned area.
     */
    public function tray(): BelongsTo
    {
        return $this->belongsTo(Tray::class);
    }
}
