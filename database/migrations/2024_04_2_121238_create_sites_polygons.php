<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSitesPolygons extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::dropIfExists('site_polygon');
        Schema::create('site_polygon', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('project_id')->nullable();
            $table->string('site_id')->nullable();
            $table->string('poly_id')->nullable();
            $table->string('poly_name')->nullable();
            $table->date('plantstart')->nullable();
            $table->date('plantend')->nullable();
            $table->string('practice')->nullable();
            $table->string('target_sys')->nullable();
            $table->string('distr')->nullable();
            $table->integer('num_trees')->nullable();
            $table->decimal('calc_area', 15, 2)->nullable();
            $table->string('status')->nullable();
            $table->string('created_by')->nullable();
            $table->string('last_modified_by')->nullable();
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
        Schema::dropIfExists('site_polygon');
    }
}