<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('project_pitches', function (Blueprint $table) {
            $table->foreignUuid('organisation_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('project_pitches', function (Blueprint $table) {
            $table->dropColumn(['organisation_id']);
        });
    }
};
