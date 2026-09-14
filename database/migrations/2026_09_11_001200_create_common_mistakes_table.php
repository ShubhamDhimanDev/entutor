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
        Schema::create('common_mistakes', function (Blueprint $table) {
            $table->id();
            $table->text('wrong_sentence');
            $table->text('corrected_sentence');
            $table->text('explanation');
            $table->string('tag');
            $table->unsignedSmallInteger('order');
            $table->timestamps();

            $table->index('tag');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('common_mistakes');
    }
};
