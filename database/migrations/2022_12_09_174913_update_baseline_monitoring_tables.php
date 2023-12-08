<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::table('baseline_monitoring_metrics_project', function (Blueprint $table) {
            $table->decimal('total_hectares')->change();
            $table->decimal('ha_mangrove')->change();
            $table->decimal('ha_assisted')->change();
            $table->decimal('ha_agroforestry')->change();
            $table->decimal('ha_reforestation')->change();
            $table->decimal('ha_peatland')->change();
            $table->decimal('ha_riparian')->change();
            $table->decimal('ha_enrichment')->change();
            $table->decimal('ha_nucleation')->change();
            $table->decimal('ha_silvopasture')->change();
            $table->decimal('ha_direct')->change();

            $table->decimal('tree_count')->change();
            $table->decimal('tree_cover')->change();
            $table->decimal('tree_cover_loss')->change();
            $table->decimal('carbon_benefits')->change();
            $table->decimal('number_of_esrp')->change();
            $table->decimal('field_tree_count')->change();
            $table->decimal('field_tree_regenerated')->change();
            $table->decimal('field_tree_survival_percent')->change();
        });

        Schema::table('baseline_monitoring_metrics_site', function (Blueprint $table) {
            $table->decimal('tree_count')->change();
            $table->decimal('tree_cover')->change();
        });
    }

    public function down()
    {
        Schema::table('baseline_monitoring_metrics_project', function (Blueprint $table) {
            $table->integer('total_hectares')->change();
            $table->integer('ha_mangrove')->change();
            $table->integer('ha_assisted')->change();
            $table->integer('ha_agroforestry')->change();
            $table->integer('ha_reforestation')->change();
            $table->integer('ha_peatland')->change();
            $table->integer('ha_riparian')->change();
            $table->integer('ha_enrichment')->change();
            $table->integer('ha_nucleation')->change();
            $table->integer('ha_silvopasture')->change();
            $table->integer('ha_direct')->change();

            $table->integer('tree_count')->change();
            $table->integer('tree_cover')->change();
            $table->integer('tree_cover_loss')->change();
            $table->integer('carbon_benefits')->change();
            $table->integer('number_of_esrp')->change();
            $table->integer('field_tree_count')->change();
            $table->integer('field_tree_regenerated')->change();
            $table->integer('field_tree_survival_percent')->change();
        });

        Schema::table('baseline_monitoring_metrics_site', function (Blueprint $table) {
            $table->integer('tree_count')->change();
            $table->integer('tree_cover')->change();
        });
    }
};
