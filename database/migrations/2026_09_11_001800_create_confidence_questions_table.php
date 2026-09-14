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
        Schema::create('confidence_questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('confidence_topic_id')->constrained('confidence_topics')->cascadeOnDelete();
            $table->text('question');
            $table->text('example_answer');
            $table->unsignedSmallInteger('order');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('confidence_questions');
    }
};
