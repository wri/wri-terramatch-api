<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateV2CoreTeamLeadersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('v2_core_team_leaders', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid');
            $table->unique('uuid');
            $table->foreignUuid('organisation_id');
            $table->index('organisation_id');
            $table->text('first_name')->nullable();
            $table->text('last_name')->nullable();
            $table->text('position')->nullable();
            $table->text('gender')->nullable();
            $table->text('role')->nullable();
            $table->unsignedTinyInteger('age')->nullable();
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
