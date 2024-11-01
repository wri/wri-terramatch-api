<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::rename('workday_demographics', 'demographics');

        Schema::table('demographics', function (Blueprint $table) {
            $table->morphs('demographical', 'demographics_morph_index');
        });

        DB::statement("UPDATE demographics SET demographical_id = workday_id, demographical_type = 'App\\\\Models\\\\V2\\\\Workdays\\\\Workday'");

        Schema::table('demographics', function (Blueprint $table) {
            $table->dropColumn('workday_id');
        });
    }

    public function down(): void
    {
        DB::statement("DELETE FROM demographics WHERE demographical_type != 'App\\\\Models\\\\V2\\\\Workdays\\\\Workday'");

        Schema::table('demographics', function (Blueprint $table) {
            $table->dropIndex('demographics_morph_index');
            $table->renameColumn('demographical_id', 'workday_id');
            $table->dropColumn('demographical_type');
        });

        Schema::rename('demographics', 'workday_demographics');
    }
};
