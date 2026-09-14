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
        Schema::create('learner_weekly_ratings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('learner_program_id')->constrained('learner_programs')->cascadeOnDelete();
            $table->foreignId('week_id')->constrained('weeks')->restrictOnDelete();
            $table->unsignedTinyInteger('reading_rating');
            $table->unsignedTinyInteger('speaking_rating');
            $table->unsignedTinyInteger('listening_rating');
            $table->unsignedTinyInteger('confidence_rating');
            $table->text('note')->nullable();
            $table->foreignId('rated_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['learner_program_id', 'week_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('learner_weekly_ratings');
    }
};
