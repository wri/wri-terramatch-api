<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFormCommonOptionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('form_common_options', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid');
            $table->string('bucket')->index();
            $table->string('slug')->nullable()->index();
            $table->string('alt_value')->nullable()->index();
            $table->string('label')->nullable();
            $table->integer('label_id')->nullable();

            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('form_common_options_questions', function (Blueprint $table) {
            $table->integer('form_questions_id')->index();
            $table->integer('form_common_options_id')->index();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('form_common_options');
        Schema::dropIfExists('form_common_options_questions');
    }
}
