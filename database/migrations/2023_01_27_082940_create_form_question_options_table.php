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
        Schema::create('form_question_options', function (Blueprint $table) {
            $table->id();
            $table->foreignId('form_question_id')->constrained()->cascadeOnDelete();
            $table->json('label');
            $table->smallInteger('order');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::dropIfExists('form_question_options');
    }
};
