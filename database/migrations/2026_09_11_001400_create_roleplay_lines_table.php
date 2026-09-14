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
        Schema::create('roleplay_lines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('roleplay_scenario_id')->constrained('roleplay_scenarios')->cascadeOnDelete();
            $table->string('side');
            $table->string('speaker_label');
            $table->text('line_text');
            $table->unsignedSmallInteger('order');
            $table->timestamps();

            $table->index(['roleplay_scenario_id', 'order']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('roleplay_lines');
    }
};
