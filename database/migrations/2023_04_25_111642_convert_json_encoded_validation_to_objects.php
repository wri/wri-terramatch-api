<?php

use App\Models\V2\Forms\FormQuestion;
use Illuminate\Database\Migrations\Migration;

class ConvertJsonEncodedValidationToObjects extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $questions = FormQuestion::whereNotNull('validation')->get();
        $questions->each(function (FormQuestion $formQuestion) {
            $formQuestion->validation = json_decode($formQuestion->validation);
            $formQuestion->save();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $questions = FormQuestion::whereNotNull('validation')->get();
        $questions->each(function (FormQuestion $formQuestion) {
            $formQuestion->validation = json_encode($formQuestion->validation);
            $formQuestion->save();
        });
    }
}
