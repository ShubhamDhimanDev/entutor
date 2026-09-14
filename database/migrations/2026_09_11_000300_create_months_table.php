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
        Schema::create('months', function (Blueprint $table) {
            $table->id();
            $table->unsignedTinyInteger('month_number')->unique();
            $table->string('theme');
            $table->json('goals');
            $table->json('expected_outcomes');
            $table->text('daily_emphasis');
            $table->json('speaking_practice_ideas');
            $table->json('reading_materials_ideas');
            $table->text('vocabulary_target');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('months');
    }
};
