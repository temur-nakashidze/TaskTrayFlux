<?php

namespace App\Observers;

use App\Models\Tag;

class TagObserver
{
    /**
     * Deleting a tag removes it from every task immediately, unblocked -
     * no "tag in use" protection. The task_tag pivot rows are plain (not
     * soft-deleted), so detaching here is a real, permanent removal even
     * though the Tag row itself is only soft-deleted and could be restored
     * later (without its old task associations).
     */
    public function deleting(Tag $tag): void
    {
        $tag->tasks()->detach();
    }
}
