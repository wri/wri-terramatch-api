<?php

use App\Models\V2\BaselineMonitoring\ProjectMetric;
use App\Models\V2\BaselineMonitoring\SiteMetric;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up()
    {
        Schema::create('baseline_monitoring_metrics_project', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->index();
            $table->string('monitorable_type')->nullable();
            $table->integer('monitorable_id')->nullable();
            $table->string('status', 30)->default(ProjectMetric::STATUS_ACTIVE);

            $table->integer('total_hectares')->nullable();
            $table->integer('ha_mangrove')->nullable();
            $table->integer('ha_assisted')->nullable();
            $table->integer('ha_agroforestry')->nullable();
            $table->integer('ha_reforestation')->nullable();
            $table->integer('ha_peatland')->nullable();
            $table->integer('ha_riparian')->nullable();
            $table->integer('ha_enrichment')->nullable();
            $table->integer('ha_nucleation')->nullable();
            $table->integer('ha_silvopasture')->nullable();
            $table->integer('ha_direct')->nullable();

            $table->integer('tree_count')->nullable();
            $table->integer('tree_cover')->nullable();
            $table->integer('tree_cover_loss')->nullable();
            $table->integer('carbon_benefits')->nullable();
            $table->integer('number_of_esrp')->nullable();
            $table->integer('field_tree_count')->nullable();
            $table->integer('field_tree_regenerated')->nullable();
            $table->float('field_tree_survival_percent')->nullable();

            $table->timestamps();
            $table->softDeletes();
            $table->index('monitorable_type', 'monitorable_id');
        });

        Schema::create('baseline_monitoring_metrics_site', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->index();
            $table->string('monitorable_type');
            $table->integer('monitorable_id');
            $table->string('status', 30)->default(SiteMetric::STATUS_ACTIVE);

            $table->integer('tree_count')->nullable();
            $table->integer('tree_cover')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index('monitorable_type', 'monitorable_id');
        });
    }

    public function down()
    {
        Schema::dropIfExists('baseline_monitoring_metrics_project');
        Schema::dropIfExists('baseline_monitoring_metrics_site');
    }
};
