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
        Schema::create('project_polygons', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('poly_id');
            $table->enum('entity', ['pitch', 'project']);
            $table->uuid('entity_uuid');
            $table->string('last_modified_by');
            $table->timestamp('deleted_at')->nullable();
            $table->timestamps();
            $table->string('created_by');

            $table->index('poly_id');
            $table->index('entity');
            $table->index('entity_uuid');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('project_polygons');
    }
}