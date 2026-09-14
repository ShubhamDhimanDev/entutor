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
        Schema::create('coach_links', function (Blueprint $table) {
            $table->id();
            $table->foreignId('learner_user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('coach_user_id')->nullable()->constrained('users')->cascadeOnDelete();
            $table->string('invite_code')->unique();
            $table->timestamp('invite_code_expires_at')->nullable();
            $table->string('status');
            $table->timestamp('redeemed_at')->nullable();
            $table->timestamp('revoked_at')->nullable();
            $table->foreignId('revoked_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['learner_user_id', 'status']);
            $table->index(['coach_user_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('coach_links');
    }
};
