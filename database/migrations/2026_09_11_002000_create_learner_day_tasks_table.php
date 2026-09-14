<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('learner_day_tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('learner_program_id')->constrained('learner_programs')->cascadeOnDelete();
            $table->foreignId('day_task_id')->constrained('day_tasks')->restrictOnDelete();
            $table->timestamp('created_at')->nullable();

            $table->unique(['learner_program_id', 'day_task_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('learner_day_tasks');
    }
};
