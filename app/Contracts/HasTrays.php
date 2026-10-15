<?php

namespace App\Contracts;

use App\Models\Project;
use Illuminate\Database\Eloquent\Relations\MorphMany;

interface HasTrays
{
    public function trays(): MorphMany;

    public function owningProject(): Project;
}
