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
        Schema::create('word_bank_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('word_bank_group_id')->constrained('word_bank_groups')->cascadeOnDelete();
            $table->string('term');
            $table->string('native_language', 5)->default('hi');
            $table->string('native_meaning');
            $table->string('native_transliteration');
            $table->text('example_sentence');
            $table->unsignedSmallInteger('order');
            $table->timestamps();

            $table->index(['word_bank_group_id', 'order']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('word_bank_entries');
    }
};
