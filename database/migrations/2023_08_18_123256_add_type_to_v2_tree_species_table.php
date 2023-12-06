<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTypeToV2TreeSpeciesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('v2_tree_species', function (Blueprint $table) {
            $table->string('type')->nullable();
            $table->string('collection')->nullable();
            $table->string('old_model')->nullable();
            $table->unsignedInteger('old_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('v2_tree_species', function (Blueprint $table) {
            $table->dropColumn(['type','collection','old_model','old_id']);
        });
    }
}
