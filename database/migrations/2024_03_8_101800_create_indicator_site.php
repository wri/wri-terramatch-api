<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateIndicatorSite extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
      Schema::create('indicator_site', function (Blueprint $table) {
        $table->id();
        $table->uuid('uuid')->unique();
        $table->integer('indicator_id');
        $table->integer('polygon_id');
        $table->integer('year');
        $table->float('value');
        $table->date('date_created');
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
      Schema::dropIfExists('indicator_site');
    }
}
