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
        Schema::create('learner_monthly_test_record_scores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('learner_monthly_test_record_id')->constrained('learner_monthly_test_records')->cascadeOnDelete();
            $table->string('skill');
            $table->unsignedTinyInteger('score');
            $table->timestamps();

            $table->unique(['learner_monthly_test_record_id', 'skill']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('learner_monthly_test_record_scores');
    }
};
