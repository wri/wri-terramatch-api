<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateV2OwnerShipStakeTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('v2_ownership_stake', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid');
            $table->foreignUuid('organisation_id');
            $table->string('first_name');
            $table->string('last_name');
            $table->string('title');
            $table->text('gender');
            $table->tinyInteger('percent_ownership');
            $table->unsignedInteger('year_of_birth');
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
        Schema::dropIfExists('v2_ownership_stake');
    }
}
