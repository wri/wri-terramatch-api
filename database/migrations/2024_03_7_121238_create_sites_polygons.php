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
      Schema::create('site_polygon', function (Blueprint $table) {
        $table->id();
        $table->uuid('uuid')->unique();
        $table->integer('project_id')->nullable();
        $table->string('project_label')->nullable();
        $table->integer('site_id')->nullable();
        $table->string('site_name')->nullable();
        $table->string('poly_label')->nullable();
        $table->date('plant_date')->nullable();
        $table->string('country')->nullable();
        $table->string('org_name')->nullable();
        $table->string('practice')->nullable();
        $table->string('target_sys')->nullable();
        $table->string('dist')->nullable();
        $table->integer('tree_count')->nullable();
        $table->float('estimated_area')->nullable();
        $table->date('date_modified')->nullable();
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
