<?php

use App\Models\V2\Forms\FormQuestion;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class MigrateAndMoveRequiredFieldToValidationOnFormQuestionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('form_questions', function (Blueprint $table) {
            $table->json('validation')->nullable();
        });
        $questions = FormQuestion::where('required', true)->get();
        $questions->each(function (FormQuestion $formQuestion) {
            $formQuestion->validation = json_encode([
                'required' => true,
            ]);
            $formQuestion->save();
        });

        Schema::table('form_questions', function (Blueprint $table) {
            $table->dropColumn('required');
        });
    }
}
