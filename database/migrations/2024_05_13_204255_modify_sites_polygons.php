<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (! Schema::hasTable('site_polygon')) {
            return;
        }

        Schema::table('site_polygon', function (Blueprint $table) {
            $columnsToDrop = ['project_id', 'proj_name', 'site_name', 'org_name', 'poly_label', 'date_modified', 'country'];

            foreach ($columnsToDrop as $column) {
                if (Schema::hasColumn('site_polygon', $column)) {
                    $table->dropColumn($column);
                }
            }

            if (Schema::hasColumn('site_polygon', 'est_area')) {
                $table->decimal('est_area', 15, 2)->nullable()->change();
                $table->renameColumn('est_area', 'calc_area');
            }

            if (! Schema::hasColumn('site_polygon', 'point_id')) {
                $table->string('point_id', 255)->nullable()->after('site_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! Schema::hasTable('site_polygon')) {
            return;
        }

        Schema::table('site_polygon', function (Blueprint $table) {
            $table->string('project_id')->nullable();
            $table->string('proj_name')->nullable();
            $table->string('site_name')->nullable();
            $table->string('org_name')->nullable();
            $table->string('poly_label')->nullable();
            $table->date('date_modified')->nullable();
            $table->string('country')->nullable();

            if (Schema::hasColumn('site_polygon', 'calc_area')) {
                $table->renameColumn('calc_area', 'est_area');
                $table->float('est_area')->nullable()->change();
            }

            $table->dropColumn('point_id');
        });
    }
};
