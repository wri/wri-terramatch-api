<?php

use App\Models\V2\Projects\Project;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint ;

class CreateV2ProjectMonitoringsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('v2_project_monitorings', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('framework_key', 20)->nullable()->index();
            $table->foreignIdFor(Project::class)->nullable();
            $table->string('status')->nullable();
            $table->float('total_hectares')->nullable();
            $table->float('ha_mangrove')->nullable();
            $table->float('ha_assisted')->nullable();
            $table->float('ha_agroforestry')->nullable();
            $table->float('ha_reforestation')->nullable();
            $table->float('ha_peatland')->nullable();
            $table->float('ha_riparian')->nullable();
            $table->float('ha_enrichment')->nullable();
            $table->float('ha_nucleation')->nullable();
            $table->float('ha_silvopasture')->nullable();
            $table->float('ha_direct')->nullable();
            $table->float('tree_count')->nullable();
            $table->float('tree_cover')->nullable();
            $table->float('tree_cover_loss')->nullable();
            $table->float('carbon_benefits')->nullable();
            $table->float('number_of_esrp')->nullable();
            $table->float('field_tree_count')->nullable();
            $table->float('field_tree_regenerated')->nullable();
            $table->float('field_tree_survival_percent')->nullable();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->date('last_updated')->nullable();

            $table->unsignedInteger('old_id')->nullable();
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
        Schema::dropIfExists('v2_project_monitorings');
    }
}
