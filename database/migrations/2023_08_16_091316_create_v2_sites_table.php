<?php

use App\Models\V2\Projects\Project;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint ;

class CreateV2SitesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('v2_sites', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('framework_key', 20)->nullable()->index();
            $table->foreignIdFor(Project::class)->nullable();
            $table->string('name')->nullable();
            $table->string('status')->default(\App\StateMachines\EntityStatusStateMachine::STARTED);
            $table->boolean('control_site')->nullable();
            $table->longText('boundary_geojson')->nullable();
            $table->text('land_use_types')->nullable();
            $table->text('restoration_strategy')->nullable();
            $table->text('description')->nullable();
            $table->text('history')->nullable();
            $table->text('land_tenures')->nullable();
            $table->text('landscape_community_contribution')->nullable();
            $table->text('planting_pattern')->nullable();
            $table->string('stratification_for_heterogeneity')->nullable();
            $table->enum('soil_condition', ['severely_degraded', 'poor', 'fair', 'good', 'no_degradation'])->nullable();
            $table->unsignedInteger('survival_rate_planted')->nullable();
            $table->unsignedInteger('direct_seeding_survival_rate')->nullable();
            $table->unsignedInteger('a_nat_regeneration_trees_per_hectare')->nullable();
            $table->unsignedInteger('a_nat_regeneration')->nullable();
            $table->unsignedInteger('hectares_to_restore_goal')->nullable();
            $table->unsignedInteger('aim_year_five_crown_cover')->nullable();
            $table->unsignedInteger('aim_number_of_mature_trees')->nullable();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();

            $table->unsignedInteger('old_id')->nullable();
            $table->string('old_model')->nullable();
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
        Schema::dropIfExists('v2_sites');
    }
}
