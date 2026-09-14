<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Optional link from a daily task into the tenses knowledge base,
     * same nullable/nullOnDelete shape as
     * day_task_vocabulary_items.word_bank_entry_id. Non-null iff
     * type = SkillArea::Grammar is an application-layer invariant
     * (seeder discipline + a Pest test), not a DB constraint — see the
     * companion migration/model notes.
     */
    public function up(): void
    {
        Schema::table('day_tasks', function (Blueprint $table) {
            $table->foreignId('tense_id')->nullable()->after('type')
                ->constrained('tenses')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('day_tasks', function (Blueprint $table) {
            $table->dropConstrainedForeignId('tense_id');
        });
    }
};
