<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * The partial/generated-column index name, shared between up() and down().
     */
    private string $index = 'coach_links_one_active_per_learner';

    /**
     * Generated-column name backing the index on drivers with no native
     * partial-index support (unused on sqlite/pgsql).
     */
    private string $activeColumn = 'active_learner_user_id';

    /**
     * Run the migrations.
     *
     * Enforces "one active coach per learner" as a hard DB-level guard against
     * a race between two simultaneous redemption requests: application code
     * already checks for an existing active link before activating a new one,
     * but only a unique index can close the gap between that check and the
     * write. SQLite and PostgreSQL support partial indexes natively via raw
     * SQL. MySQL/MariaDB have no partial-index syntax, so a stored generated
     * column that is NULL unless the row is active stands in for one — unique
     * indexes treat NULLs as distinct, so only one 'active' row per learner
     * is allowed.
     */
    public function up(): void
    {
        if (in_array(Schema::getConnection()->getDriverName(), ['sqlite', 'pgsql'], true)) {
            DB::statement(
                "CREATE UNIQUE INDEX {$this->index} ON coach_links (learner_user_id) WHERE status = 'active'",
            );

            return;
        }

        Schema::table('coach_links', function (Blueprint $table) {
            $table->unsignedBigInteger($this->activeColumn)
                ->nullable()
                ->storedAs("CASE WHEN status = 'active' THEN learner_user_id END")
                ->after('learner_user_id');
        });

        DB::statement("CREATE UNIQUE INDEX {$this->index} ON coach_links ({$this->activeColumn})");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (in_array(Schema::getConnection()->getDriverName(), ['sqlite', 'pgsql'], true)) {
            DB::statement("DROP INDEX IF EXISTS {$this->index}");

            return;
        }

        Schema::table('coach_links', function (Blueprint $table) {
            $table->dropIndex($this->index);
            $table->dropColumn($this->activeColumn);
        });
    }
};
