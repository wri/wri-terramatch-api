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
        Schema::table('form_question_options', function (Blueprint $table) {
            $table->string('slug')->nullable()->after('form_question_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('form_question_options', function (Blueprint $table) {
            $table->dropColumn(['slug']);
        });
    }
};
