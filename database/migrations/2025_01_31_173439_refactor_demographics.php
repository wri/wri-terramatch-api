<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::rename('demographics', 'demographic_entries');
        Schema::table('demographic_entries', function (Blueprint $table) {
            // Can't drop demographical_type until the data migration script has run. Will be addressed in TM-1682
            $table->renameColumn('demographical_id', 'demographic_id');
        });

        Schema::rename('v2_workdays', 'demographics');
        Schema::table('demographics', function (Blueprint $table) {
            $table->renameColumn('workdayable_type', 'demographical_type');
            $table->renameColumn('workdayable_id', 'demographical_id');

            $table->dropColumn('framework_key');
            $table->dropColumn('amount');
            $table->dropColumn('gender');
            $table->dropColumn('age');
            $table->dropColumn('ethnicity');
            $table->dropColumn('indigeneity');
            $table->dropColumn('migrated_to_demographics');

            $table->string('type')->nullable(false);
        });
        DB::statement('UPDATE demographics SET type = "workdays"');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::rename('demographics', 'v2_workdays');
        Schema::table('demographics', function (Blueprint $table) {
            $table->renameColumn('demographical_type', 'workdayable_type');
            $table->renameColumn('demographical_id', 'workdayable_id');
        });

        Schema::rename('demographic_entries', 'demographics');
        Schema::table('demographic_entries', function (Blueprint $table) {
            $table->renameColumn('demographic_id', 'demographical_id');
        });
    }
};
