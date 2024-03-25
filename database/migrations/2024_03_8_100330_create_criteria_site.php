<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCriteriaSite extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
      Schema::create('criteria_site', function (Blueprint $table) {
        $table->id();
        $table->uuid('uuid')->unique();
        $table->integer('criteria_id')->nullable();
        $table->integer('polygon_id')->nullable();
        $table->integer('valid')->nullable();
        $table->date('date_created')->nullable();
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
      Schema::dropIfExists('criteria_site');
    }
}