<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Global knowledge-base table, one row per canonical tense (fixed
     * cardinality of 12 for v1 — present/past/future x
     * simple/continuous/perfect/perfect-continuous). Seeder-authored,
     * identical for every learner, same as Month/Week/Day/WordBankGroup.
     */
    public function up(): void
    {
        Schema::create('tenses', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->string('name');
            $table->string('time');
            $table->string('aspect');
            $table->text('summary');
            $table->string('structure_affirmative');
            $table->string('structure_negative');
            $table->string('structure_interrogative');
            $table->json('usage_rules');
            $table->json('signal_words');
            $table->json('examples');
            $table->text('common_confusions')->nullable();
            $table->unsignedTinyInteger('order')->unique();
            $table->timestamps();

            $table->index('time');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tenses');
    }
};
