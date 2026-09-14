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
        Schema::create('monthly_test_sections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('monthly_test_id')->constrained('monthly_tests')->cascadeOnDelete();
            $table->string('skill');
            $table->unsignedTinyInteger('weight');
            $table->text('content');
            $table->unsignedTinyInteger('order');
            $table->timestamps();

            $table->unique(['monthly_test_id', 'skill']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('monthly_test_sections');
    }
};
