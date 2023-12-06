<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateV2LeadershipTeamTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('v2_leadership_team', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid');
            $table->foreignUuid('organisation_id');
            $table->text('position');
            $table->text('gender');
            $table->unsignedTinyInteger('age');
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
        Schema::dropIfExists('v2_leadership_team');
    }
}
