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
        Schema::create('monthly_tests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('month_id')->unique()->constrained('months')->cascadeOnDelete();
            $table->unsignedTinyInteger('band_excellent_min')->default(80);
            $table->unsignedTinyInteger('band_good_min')->default(60);
            $table->unsignedTinyInteger('band_fair_min')->default(40);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('monthly_tests');
    }
};
