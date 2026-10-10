<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('trays', function (Blueprint $table) {
            $table->id();

            // A Tray can belong to a Project, a Shelf, or a Workbench.
            // Polymorphic instead of three separate nullable foreign keys.
            $table->string('trayable_type')->nullable();
            $table->unsignedBigInteger('trayable_id')->nullable();
            $table->string('name')->nullable();
            $table->string('slug')->nullable();
            $table->string('color', 30)->nullable(); // Tailwind color token (e.g. 'primary', 'accent', 'sky'), not a hex code
            $table->unsignedInteger('position')->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->index(
                ['trayable_type', 'trayable_id', 'position'],
                'trays_trayable_position_index',
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('trays');
    }
};
