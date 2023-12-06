<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateV2WorkdaysTable extends Migration
{
    /**
     * This migration seeds the default options
     *
     * @return void
     */
    public function up()
    {
        Schema::create('v2_workdays', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid');
            $table->morphs('workdayable');
            $table->string('framework_key')->nullable();
            $table->integer('amount')->nullable();
            $table->string('role')->nullable();
            $table->string('gender')->nullable();
            $table->string('age')->nullable();
            $table->string('ethnicity')->nullable();

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
        Schema::dropIfExists('v2_workdays');
    }
}
