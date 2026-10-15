<?php

namespace App\Models;

use App\Enums\TagColor;
use App\Observers\TagObserver;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Table('tags')]
#[Fillable(['user_id', 'name', 'color'])]
#[ObservedBy([TagObserver::class])]
class Tag extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected function casts(): array
    {
        return ['color' => TagColor::class];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function tasks(): BelongsToMany
    {
        return $this->belongsToMany(Task::class, 'task_tag');
    }
}
