<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::dropIfExists('code_indicator');
        Schema::dropIfExists('indicator_output_tree_cover_loss');
        Schema::dropIfExists('indicator_output_hectares');
        Schema::dropIfExists('indicator_output_tree_count');
        Schema::dropIfExists('indicator_output_tree_cover');
        Schema::dropIfExists('indicator_output_field_monitoring');
        Schema::dropIfExists('indicator_output_msu_carbon');
        Schema::create('code_indicator', function (Blueprint $table) {
            $table->id();
            $table->integer('id_primary')->nullable();
            $table->string('name')->nullable();
            $table->string('description')->nullable();
            $table->string('unit')->nullable();
            $table->date('date_created')->nullable();
            $table->date('date_ended')->nullable();
            $table->boolean('is_active')->nullable();
            $table->string('output_table')->nullable();
            $table->softDeletes();
        });
        Schema::create('indicator_output_tree_cover_loss', function (Blueprint $table) {
            $table->id();
            $table->enum('indicator_id', [2, 3])->nullable();
            $table->string('polygon_id')->nullable();
            $table->string('year_of_analysis')->nullable();
            $table->string('value')->nullable();
            $table->date('date_created')->nullable();
            $table->softDeletes();
        });
        Schema::create('indicator_output_hectares', function (Blueprint $table) {
            $table->id();
            $table->enum('indicator_id', [4, 5, 6])->nullable();
            $table->string('polygon_id')->nullable();
            $table->string('year_of_analysis')->nullable();
            $table->string('value')->nullable();
            $table->date('date_created')->nullable();
            $table->softDeletes();
        });
        Schema::create('indicator_output_tree_count', function (Blueprint $table) {
            $table->id();
            $table->enum('indicator_id', [7, 8])->nullable();
            $table->string('polygon_id')->nullable();
            $table->string('survey_type')->nullable();
            $table->integer('survey_id')->nullable();
            $table->string('year_of_analysis')->nullable();
            $table->integer('tree_count')->nullable();
            $table->string('uncertainty_type')->nullable();
            $table->string('imagery_source')->nullable();
            $table->date('collection_date')->nullable();
            $table->string('imagery_id')->nullable();
            $table->string('project_phase')->nullable();
            $table->integer('confidence')->nullable();
            $table->date('date_created')->nullable();
            $table->softDeletes();
        });
        Schema::create('indicator_output_tree_cover', function (Blueprint $table) {
            $table->id();
            $table->enum('indicator_id', [1])->nullable();
            $table->string('polygon_id')->nullable();
            $table->string('year_of_analysis')->nullable();
            $table->integer('percent_cover')->nullable();
            $table->string('project_phase')->nullable();
            $table->integer('plus_minus_percent')->nullable();
            $table->date('date_created')->nullable();
            $table->softDeletes();
        });
        Schema::create('indicator_output_field_monitoring', function (Blueprint $table) {
            $table->id();
            $table->enum('indicator_id', [9])->nullable();
            $table->string('polygon_id')->nullable();
            $table->string('year_of_analysis')->nullable();
            $table->integer('tree_count')->nullable();
            $table->string('project_phase')->nullable();
            $table->date('date_created')->nullable();
            $table->string('species')->nullable();
            $table->string('value')->nullable();
            $table->integer('survival_rate')->nullable();
            $table->softDeletes();
        });
        Schema::create('indicator_output_msu_carbon', function (Blueprint $table) {
            $table->id();
            $table->enum('indicator_id', [10])->nullable();
            $table->string('polygon_id')->nullable();
            $table->string('year_of_analysis')->nullable();
            $table->integer('carbon_ouput')->nullable();
            $table->string('project_phase')->nullable();
            $table->integer('confidence')->nullable();
            $table->date('date_created')->nullable();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('indicator_output_tree_cover_loss');
        Schema::dropIfExists('indicator_output_hectares');
        Schema::dropIfExists('indicator_output_tree_count');
        Schema::dropIfExists('indicator_output_tree_cover');
        Schema::dropIfExists('indicator_output_field_monitoring');
        Schema::dropIfExists('indicator_output_msu_carbon');
    }
};
