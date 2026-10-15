<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('workbench_task', function (Blueprint $table) {
            $table->id();
            $table->foreignId('workbench_id')->constrained()->cascadeOnDelete();
            $table->foreignId('task_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('position')->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['workbench_id', 'position']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('workbench_task');
    }
};
