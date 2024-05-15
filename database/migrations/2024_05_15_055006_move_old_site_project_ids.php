<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::table('v2_sites')
            ->where('old_model', 'App\Models\Terrafund\TerrafundSite')
            ->update(['old_id' => null]);
        DB::table('v2_projects')
            ->where('old_model', 'App\Models\Terrafund\TerrafundProgramme')
            ->update(['old_id' => null]);

        Schema::table('v2_sites', function (Blueprint $table) {
            $table->renameColumn('old_id', 'ppc_external_id');
            $table->dropColumn('old_model');
        });
        Schema::table('v2_sites', function (Blueprint $table) {
            $table->unsignedInteger('ppc_external_id')->unique()->change();

            // These triggers ensure that we always get a unique ppc_external_id set for every ppc site
            DB::unprepared('
                CREATE TRIGGER before_insert_v2_sites BEFORE INSERT ON v2_sites 
                FOR EACH ROW 
                BEGIN
                    IF (NEW.framework_key = \'ppc\') THEN
                        SET NEW.ppc_external_id = (SELECT max(ppc_external_id) + 1 FROM v2_sites);
                    END IF;
                END;
            ');
            DB::unprepared('
                CREATE TRIGGER before_update_v2_sites BEFORE UPDATE ON v2_sites 
                FOR EACH ROW 
                BEGIN
                    IF (NEW.framework_key = \'ppc\' AND OLD.framework_key != \'pcc\' AND NEW.ppc_external_id IS NULL) THEN
                        SET NEW.ppc_external_id = (SELECT max(ppc_external_id) + 1 FROM v2_sites);
                    END IF;
                END;
            ');
        });

        Schema::table('v2_projects', function (Blueprint $table) {
            $table->renameColumn('old_id', 'ppc_external_id');
            $table->dropColumn('old_model');
        });
        Schema::table('v2_projects', function (Blueprint $table) {
            $table->unsignedInteger('ppc_external_id')->unique()->change();

            // These triggers ensure that we always get a unique ppc_external_id set for every ppc project
            DB::unprepared('
                CREATE TRIGGER before_insert_v2_projects BEFORE INSERT ON v2_projects 
                FOR EACH ROW 
                BEGIN
                    IF (NEW.framework_key = \'ppc\') THEN
                        SET NEW.ppc_external_id = (SELECT max(ppc_external_id) + 1 FROM v2_projects);
                    END IF;
                END;
            ');
            DB::unprepared('
                CREATE TRIGGER before_update_v2_projects BEFORE UPDATE ON v2_projects 
                FOR EACH ROW 
                BEGIN
                    IF (NEW.framework_key = \'ppc\' AND OLD.framework_key != \'pcc\' AND NEW.ppc_external_id IS NULL) THEN
                        SET NEW.ppc_external_id = (SELECT max(ppc_external_id) + 1 FROM v2_projects);
                    END IF;
                END;
            ');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('v2_sites', function (Blueprint $table) {
            $table->renameColumn('ppc_external_id', 'old_id');
            $table->unsignedInteger('ppc_external_id')->change();
        });
        Schema::table('v2_projects', function (Blueprint $table) {
            $table->renameColumn('ppc_external_id', 'old_id');
            $table->unsignedInteger('ppc_external_id')->change();
        });
        DB::unprepared('
            DROP TRIGGER IF EXISTS before_insert_v2_sites;
            DROP TRIGGER IF EXISTS before_update_v2_sites;
            DROP TRIGGER IF EXISTS before_insert_v2_projects;
            DROP TRIGGER IF EXISTS before_update_v2_projects;
        ');
    }
};
