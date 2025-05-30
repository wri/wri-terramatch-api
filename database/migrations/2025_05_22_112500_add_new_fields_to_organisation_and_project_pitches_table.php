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
        //
        Schema::table('organisations', function (Blueprint $table) {
            $table->boolean('associations_cooperatives')->nullable();
            $table->string('territories_of_operation')->nullable();
            $table->text('decisionmaking_structure_description')->nullable();
            $table->text('decisionmaking_structure_individuals_involved')->nullable();
            $table->decimal('average_worker_income', 15, 2)->nullable();
            $table->string('anr_practices_past')->nullable();
            $table->string('anr_monitoring_approaches')->nullable();
            $table->text('anr_monitoring_approaches_description')->nullable();
            $table->text('anr_communication_funders')->nullable();
            $table->text('bioeconomy_products')->nullable();
            $table->text('bioeconomy_traditional_knowledge')->nullable();
            $table->text('bioeconomy_product_processing')->nullable();
            $table->text('bioeconomy_buyers')->nullable();
        });

        Schema::table('project_pitches', function (Blueprint $table) {
            $table->integer('forest_fragments_distance')->nullable();
            $table->string('anr_practices_proposed')->nullable();
            $table->boolean('information_authorization')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
        Schema::table('organisations', function (Blueprint $table) {
            $table->dropColumn('associations_cooperatives');
            $table->dropColumn('territories_of_operation');
            $table->dropColumn('decisionmaking_structure_description');
            $table->dropColumn('decisionmaking_structure_individuals_involved');
            $table->dropColumn('average_worker_income');
            $table->dropColumn('anr_practices_past');
            $table->dropColumn('anr_monitoring_approaches');
            $table->dropColumn('anr_monitoring_approaches_description');
            $table->dropColumn('anr_communication_funders');
            $table->dropColumn('bioeconomy_products');
            $table->dropColumn('bioeconomy_traditional_knowledge');
            $table->dropColumn('bioeconomy_product_processing');
            $table->dropColumn('bioeconomy_buyers');
        });

        Schema::table('project_pitches', function (Blueprint $table) {
            $table->dropColumn('forest_fragments_distance');
            $table->dropColumn('anr_practices_proposed');
            $table->dropColumn('information_authorization');
        });
    }
};
