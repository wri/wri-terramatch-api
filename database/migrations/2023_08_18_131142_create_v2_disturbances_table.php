<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateV2DisturbancesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('v2_disturbances', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->morphs('disturbanceable');
            $table->enum('kind', ['disturbance','invasive'])->nullable();
            $table->string('type')->nullable();
            $table->string('intensity')->nullable();
            $table->text('description')->nullable();

            $table->unsignedInteger('old_id')->nullable();
            $table->string('old_model')->nullable();
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
        Schema::dropIfExists('v2_disturbances');
    }
}
