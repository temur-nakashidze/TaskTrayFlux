<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('workbenches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->unique()->constrained()->cascadeOnDelete();
            $table->timestamps();

            // No deleted_at: a Workbench has no independent lifecycle, it is created
            // alongside its Project and only ever destroyed as part of the Project's cascade.
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('workbenches');
    }
};
