<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tasks', function (Blueprint $table) {
            $table->id();

            // Denormalized for cheap "all tasks in project/shelf" queries and for the
            // Dropzone view (project_id null). Kept in sync in the app layer whenever
            // a task's location changes.
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table
                ->foreignId('project_id')
                ->nullable()
                ->constrained()
                ->cascadeOnDelete();
            $table
                ->foreignId('tray_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();
            $table
                ->foreignId('shelf_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();
            $table->text('title');
            $table->text('note')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->unsignedInteger('duration')->nullable();
            $table->boolean('blocked_at')->nullable();
            $table->unsignedInteger('position')->default(0);
            $table->timestamps();
            $table->softDeletes();

            // project_id/shelf_id/tray_id cascades/null-on-delete are a safety net only;
            // the app's cascading soft-delete/restore observers are the real source of truth,
            // since these are soft deletes, not hard deletes.
            $table->index(['user_id', 'project_id']);
            $table->index(['project_id', 'tray_id', 'position']);
            $table->index(['user_id', 'completed']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tasks');
    }
};
