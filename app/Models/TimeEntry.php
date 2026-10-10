<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Model;

#[Fillable("user_id", "task_id", "started_at", "ended_at")]
class TimeEntry extends Model
{
    protected $casts = ["started_at" => "datetime", "ended_at" => "datetime"];

    public function task()
    {
        return $this->belongsTo(Task::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    #[Scope]
    public function running()
    {
        return $this->ended_at === null;
    }
}
