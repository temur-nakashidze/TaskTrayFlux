<?php

namespace App\Models;

use App\Concerns\HasPosition;
use App\Contracts\HasTrays;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable(['name'])]
class Shelf extends Model implements HasTrays
{
    use HasPosition;
    use SoftDeletes;

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
}
