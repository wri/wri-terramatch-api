<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFieldsToOrgAndPitch extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('organisations', function (Blueprint $table) {
            $table->text('restoration_types_implemented')->nullable()->after('restored_areas_description');
            $table->text('historic_monitoring_geojson')->nullable()->after('restoration_types_implemented');
        });

        Schema::table('project_pitches', function (Blueprint $table) {
            $table->text('main_degradation_causes')->nullable()->after('curr_land_degradation');
            $table->text('seedlings_source')->nullable()->after('main_degradation_causes');
        });

        Schema::table('v2_funding_types', function (Blueprint $table) {
            $table->string('source')->nullable()->after('amount');
        });

        Schema::table('v2_leadership_team', function (Blueprint $table) {
            $table->string('first_name')->nullable()->after('id');
            $table->string('last_name')->nullable()->after('first_name');
        });

        Schema::create('form_table_headers', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid');
            $table->foreignId('form_question_id')->constrained()->cascadeOnDelete();
            $table->string('slug')->nullable();
            $table->string('label')->nullable();
            $table->integer('label_id')->nullable();
            $table->smallInteger('order')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('organisations', function (Blueprint $table) {
            $table->dropColumn([
                'restoration_types_implemented',
                'historic_monitoring_geojson',
            ]);
        });

        Schema::table('project_pitches', function (Blueprint $table) {
            $table->dropColumn([
                'main_degradation_causes',
                'seedlings_source',
            ]);
        });

        Schema::table('v2_funding_types', function (Blueprint $table) {
            $table->dropColumn([
                'source',
            ]);
        });

        Schema::table('v2_leadership_team', function (Blueprint $table) {
            $table->dropColumn([
                'first_name',
                'last_name',
            ]);
        });
    }
}
