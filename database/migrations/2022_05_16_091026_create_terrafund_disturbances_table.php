<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('terrafund_disturbances', function (Blueprint $table) {
            $table->id();
            $table->string('type', 255);
            $table->text('description');
            $table->morphs('disturbanceable', 'disturbanceable_index');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::dropIfExists('terrafund_disturbances');
    }
};
