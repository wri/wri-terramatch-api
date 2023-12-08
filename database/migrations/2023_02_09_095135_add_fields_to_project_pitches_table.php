<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('project_pitches', function (Blueprint $table) {
            $table->string('project_name')->nullable()->after('organisation_id');
            $table->mediumText('project_objectives')->nullable()->after('organisation_id');
            $table->string('project_country')->nullable()->after('organisation_id');
            $table->string('project_county_district')->nullable()->after('organisation_id');
            $table->text('restoration_intervention_types')->nullable()->after('organisation_id');
            $table->unsignedInteger('total_hectares')->nullable()->after('organisation_id');
            $table->unsignedInteger('total_trees')->nullable()->after('organisation_id');
            $table->text('capacity_building_needs')->nullable()->after('organisation_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('project_pitches', function (Blueprint $table) {
            $table->dropColumn([
                'project_name',
                'project_objectives',
                'project_country',
                'project_county_district',
                'restoration_intervention_types',
                'total_hectares',
                'total_trees',
                'capacity_building_needs',
            ]);
        });
    }
};
