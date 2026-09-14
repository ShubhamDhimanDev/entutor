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
        Schema::create('learner_monthly_test_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('learner_program_id')->constrained('learner_programs')->cascadeOnDelete();
            $table->foreignId('month_id')->constrained('months')->restrictOnDelete();
            $table->date('taken_on');
            $table->unsignedTinyInteger('total_score');
            $table->foreignId('recorded_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['learner_program_id', 'month_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('learner_monthly_test_records');
    }
};
