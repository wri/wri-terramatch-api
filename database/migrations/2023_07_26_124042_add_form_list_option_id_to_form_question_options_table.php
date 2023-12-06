<?php

use App\Models\V2\Forms\FormOptionListOption;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFormListOptionIdToFormQuestionOptionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('form_question_options', function (Blueprint $table) {
            $table->foreignIdFor(FormOptionListOption::class)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('form_question_options', function (Blueprint $table) {
            $table->dropForeign('form_option_list_option_id');
        });
    }
}
