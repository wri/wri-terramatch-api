<?php

use App\Models\V2\Forms\Application;
use App\Models\V2\Organisation;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateV2ProjectsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('v2_projects', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('framework_key', 20)->nullable()->index();
            $table->foreignIdFor(Organisation::class)->nullable();
            $table->foreignIdFor(Application::class)->nullable();
            $table->string('status')->nullable();
            $table->enum('project_status', ['new_project', 'existing_expansion'])->nullable();
            $table->text('name')->nullable();
            $table->longText('boundary_geojson')->nullable();
            $table->text('land_use_types')->nullable();
            $table->text('restoration_strategy')->nullable();
            $table->text('country')->nullable();
            $table->text('continent')->nullable();
            $table->date('planting_start_date')->nullable();
            $table->date('planting_end_date')->nullable();
            $table->text('description')->nullable();
            $table->text('history')->nullable();
            $table->text('objectives')->nullable();
            $table->text('environmental_goals')->nullable();
            $table->text('socioeconomic_goals')->nullable();
            $table->text('sdgs_impacted')->nullable();
            $table->text('long_term_growth')->nullable();
            $table->text('community_incentives')->nullable();
            $table->unsignedInteger('budget')->nullable();
            $table->unsignedInteger('jobs_created_goal')->nullable();
            $table->unsignedInteger('total_hectares_restored_goal')->nullable();
            $table->unsignedInteger('trees_grown_goal')->nullable();
            $table->unsignedInteger('survival_rate')->nullable();
            $table->unsignedInteger('year_five_crown_cover')->nullable();
            $table->unsignedInteger('monitored_tree_cover')->nullable();

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
        Schema::dropIfExists('v2_projects');
    }
}
