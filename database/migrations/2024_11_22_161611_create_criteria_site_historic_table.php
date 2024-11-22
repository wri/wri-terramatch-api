<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCriteriaSiteHistoricTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('criteria_site_historic', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->integer('criteria_id')->nullable();
            $table->string('polygon_id')->nullable();
            $table->integer('valid')->nullable();
            $table->json('extra_info')->nullable();
            $table->date('date_created')->nullable();
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
        Schema::dropIfExists('criteria_site_historic');
    }
}
