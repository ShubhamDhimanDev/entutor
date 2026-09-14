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
        Schema::create('weeks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('month_id')->constrained('months')->cascadeOnDelete();
            $table->unsignedTinyInteger('week_number')->unique();
            $table->unsignedTinyInteger('week_in_month');
            $table->unsignedTinyInteger('start_day_number');
            $table->unsignedTinyInteger('end_day_number');
            $table->string('title');
            $table->text('mastery_description');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('weeks');
    }
};
