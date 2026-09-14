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
        Schema::create('roleplay_scenarios', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('domain');
            $table->text('tip');
            $table->unsignedSmallInteger('order');
            $table->timestamps();

            $table->index('domain');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('roleplay_scenarios');
    }
};
