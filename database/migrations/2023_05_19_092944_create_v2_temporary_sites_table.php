<?php

use App\Models\Programme;
use App\Models\Site;
use App\Models\Terrafund\TerrafundSite;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateV2TemporarySitesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('v2_temporary_sites', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid');
            $table->foreignIdFor(Programme::class)->nullable();
            $table->foreignIdFor(Site::class)->nullable();
            $table->foreignIdFor(TerrafundSite::class)->nullable();
            $table->tinyInteger('control_site')->nullable();
            $table->text('name');
            $table->text('country')->nullable();
            $table->text('project_country')->nullable();
            $table->text('continent')->nullable();
            $table->text('description')->nullable();
            $table->text('planting_pattern')->nullable();
            $table->text('stratification_for_heterogeneity')->nullable();
            $table->text('history')->nullable();
            $table->unsignedInteger('workdays_paid')->nullable();
            $table->unsignedInteger('workdays_volunteer')->nullable();
            $table->date('establishment_date')->nullable();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->longText('restoration_methods')->nullable();
            $table->longText('land_tenures')->nullable();
            $table->text('technical_narrative')->nullable();
            $table->text('public_narrative')->nullable();
            $table->tinyInteger('aim_survival_rate')->nullable();
            $table->tinyInteger('aim_year_five_crown_cover')->nullable();
            $table->tinyInteger('aim_direct_seeding_survival_rate')->nullable();
            $table->integer('aim_natural_regeneration_trees_per_hectare')->nullable();
            $table->integer('aim_natural_regeneration_hectares')->nullable();
            $table->text('aim_soil_condition')->nullable();
            $table->integer('aim_number_of_mature_trees')->nullable();
            $table->integer('hectares_to_restore')->nullable();
            $table->text('landscape_community_contribution')->nullable();
            $table->text('disturbances')->nullable();
            $table->longText('boundary_geojson')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('v2_temporary_sites');
    }
}
