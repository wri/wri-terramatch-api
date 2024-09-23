<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up()
    {
        Schema::create('terrafund_none_tree_species', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->bigInteger('amount');
            $table->morphs('speciesable', 'none_tree_species_moph_index');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('terrafund_none_tree_species');
    }
};
