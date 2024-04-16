<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCodeIndicator extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::dropIfExists('code_indicator');
        Schema::create('code_indicator', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('uuid_primary');
            $table->string('name');
            $table->string('unit');
            $table->string('description');
            $table->integer('is_active');
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
        Schema::dropIfExists('code_indicator');
    }
}
