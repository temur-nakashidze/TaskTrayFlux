<?php

namespace App\Observers;

use App\Models\Project;

class ProjectObserver
{
    /**
     * Every project gets exactly one Workbench, created automatically.
     * It has no lifecycle of its own beyond this - see Workbench model.
     */
    public function created(Project $project): void
    {
        $project->workbench()->create();
        $project->blueprint()->create();
        $project->trays()->create([
            'name' => 'Unsorted',
            'position' => 0,
        ]);
    }

    /**
     * Cascade a soft-delete down to everything that lives under this project.
     * Order matters: delete tasks first (which cascades to their Cuts and
     * Workbench references via TaskObserver) before removing the containers
     * they sit in.
     */
    public function deleting(Project $project): void
    {
        $project->trays()->get()->each(fn ($tray) => $tray->delete());

        if ($workbench = $project->workbench) {
            $workbench->workbenchTasks()->get()->each(fn ($wt) => $wt->delete());
            $workbench->delete;
        }
    }

    /**
     * Restoring a project cascades restore back down the same tree.
     *
     * Note: this restores every trashed child, since there is no reliable
     * way to distinguish "deleted only because its parent was deleted" from
     * "deleted individually, shortly before the parent." If you need that
     * distinction later, compare deleted_at timestamps between parent and
     * child before restoring.
     */
    public function restoring(Project $project): void
    {
        $project
            ->trays()
            ->onlyTrashed()
            ->get()
            ->each(fn ($tray) => $tray->restore());

        $project->workbench
            ->workbenchTasks()
            ->onlyTrashed()
            ->get()
            ->each(fn ($wt) => $wt->restore());

    }
}
