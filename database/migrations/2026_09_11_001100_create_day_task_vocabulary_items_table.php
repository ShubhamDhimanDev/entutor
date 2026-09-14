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
        Schema::create('day_task_vocabulary_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('day_task_id')->constrained('day_tasks')->cascadeOnDelete();
            $table->foreignId('word_bank_entry_id')->nullable()->constrained('word_bank_entries')->nullOnDelete();
            $table->string('term');
            $table->string('native_meaning');
            $table->unsignedTinyInteger('order');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('day_task_vocabulary_items');
    }
};
