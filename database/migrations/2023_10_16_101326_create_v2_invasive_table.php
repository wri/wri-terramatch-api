<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateV2InvasiveTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('v2_invasives', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid');
            $table->morphs('invasiveable');
            $table->string('type')->nullable();
            $table->string('name')->nullable();
            $table->string('collection')->nullable();

            $table->softDeletes();
            $table->timestamps();
        });

        Schema::table('v2_disturbances', function (Blueprint $table) {
            $table->dropColumn('kind');
            $table->string('extent')->nullable()->after('intensity');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('v2_invasives');
    }
}
