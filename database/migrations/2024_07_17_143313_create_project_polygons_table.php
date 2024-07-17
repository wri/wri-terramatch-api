<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProjectPolygonsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::dropIfExists('project_polygon');
        Schema::create('project_polygon', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('poly_uuid');
            $table->morphs('entity');
            $table->string('last_modified_by');
            $table->string('created_by');

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
        Schema::dropIfExists('project_polygon');
    }
}