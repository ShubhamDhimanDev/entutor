<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * The partial index name, shared between up() and down().
     */
    private string $index = 'coach_links_one_active_per_learner';

    /**
     * Run the migrations.
     *
     * Enforces "one active coach per learner" as a hard DB-level guard against
     * a race between two simultaneous redemption requests: application code
     * already checks for an existing active link before activating a new one,
     * but only a unique index can close the gap between that check and the
     * write. SQLite (the only driver this app targets) supports partial
     * indexes via raw SQL; the query builder has no portable helper for one.
     */
    public function up(): void
    {
        DB::statement(
            "CREATE UNIQUE INDEX {$this->index} ON coach_links (learner_user_id) WHERE status = 'active'",
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("DROP INDEX IF EXISTS {$this->index}");
    }
};
