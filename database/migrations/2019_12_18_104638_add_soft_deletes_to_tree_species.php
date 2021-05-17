<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSoftDeletesToTreeSpecies extends Migration
{
    public function up()
    {
        Schema::table('tree_species_versions', function (Blueprint $table) {
            $table->softDeletes();
        });
    }

    public function down()
    {
    }
}
