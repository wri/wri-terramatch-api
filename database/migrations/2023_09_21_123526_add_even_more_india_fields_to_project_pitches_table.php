<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddEvenMoreIndiaFieldsToProjectPitchesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('project_pitches', function (Blueprint $table) {
            $table->text('theory_of_change')->nullable();
            $table->text('proposed_gov_partners')->nullable();
            $table->integer('pct_sch_tribe')->nullable();
            $table->text('sustainability_plan')->nullable();
            $table->text('replication_plan')->nullable();
            $table->text('replication_challenges')->nullable();
            $table->text('solution_market_size')->nullable();
            $table->text('affordability_of_solution')->nullable();
            $table->text('growth_trends_business')->nullable();
            $table->text('limitations_on_scope')->nullable();
            $table->text('business_model_replication_plan')->nullable();
            $table->text('biodiversity_impact')->nullable();
            $table->text('water_source')->nullable();
            $table->text('climate_resilience')->nullable();
            $table->text('soil_health')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('project_pitches', function (Blueprint $table) {
            $table->dropColumn([
                'theory_of_change',
                'proposed_gov_partners',
                'pct_sch_tribe',
                'sustainability_plan',
                'replication_plan',
                'replication_challenges',
                'solution_market_size',
                'affordability_of_solution',
                'growth_trends_business',
                'limitations_on_scope',
                'business_model_replication_plan',
                'biodiversity_impact',
                'biodiversity_impact',
                'water_source',
                'climate_resilience',
                'soil_health',
            ]);
        });
    }
}
