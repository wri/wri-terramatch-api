<?php

namespace Database\Seeders;

use App\Helpers\I18nHelper;
use App\Models\V2\Forms\Form;
use Illuminate\Database\Seeder;

class FormsSubmissionMessageTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $forms = Form::all();
        $forms->each(function ($form) {
            $form->submission_message_id = I18nHelper::generateI18nItem($form, 'submission_message');
            $form->save();
        });

    }
}
