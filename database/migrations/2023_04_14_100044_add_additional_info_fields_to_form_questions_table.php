<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAdditionalInfoFieldsToFormQuestionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('form_questions', function (Blueprint $table) {
            $table->string('additional_url')->after('additional_props')->nullable();
            $table->tinyText('additional_text')->after('additional_props')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('form_questions', function (Blueprint $table) {
            $table->dropColumn([
                'additional_text',
                'additional_url',
            ]);
        });
    }
}
