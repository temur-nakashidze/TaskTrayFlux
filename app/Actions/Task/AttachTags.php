<?php

namespace App\Actions\Task;

use App\Enums\TagColor;
use App\Models\Task;
use App\Models\User;

final class AttachTags
{
    /** @param  array<int, array{id: ?int, name: string}>  $tags */
    public function handle(Task $task, User $user, array $tags): void
    {
        $tags = collect($tags);

        // existing tags, re-checked against the user's own tags
        $ids = $user->tags()->whereKey($tags->pluck('id')->filter()->all())->pluck('id');

        // new names, or names picked after being created a moment ago
        foreach ($tags->whereNull('id')->pluck('name') as $name) {
            $name = mb_substr(trim($name), 0, 50);

            if ($name !== '') {
                $tag = $user->tags()
                    ->withTrashed()
                    ->firstOrNew(['name' => $name], ['color' => TagColor::random()]);

                if (! $tag->exists) {
                    $tag->save();
                } elseif ($tag->trashed()) {
                    $tag->restore();
                }

                $ids->push($tag->id);
            }
        }

        $task->tags()->syncWithoutDetaching($ids->unique()->all());
    }
}
