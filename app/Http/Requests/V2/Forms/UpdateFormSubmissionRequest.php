<?php

namespace App\Http\Requests\V2\Forms;

use App\Models\V2\Forms\FormQuestion;
use App\Models\V2\Forms\FormQuestionOption;
use App\Models\V2\Forms\FormSection;
use App\Models\V2\Forms\FormSubmission;
use Illuminate\Foundation\Http\FormRequest;

class UpdateFormSubmissionRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules()
    {
        return [
            'name' => ['sometimes', 'string', 'max:65000'],
            'status' => ['sometimes', 'string', 'in:' . implode(',', array_keys(FormSubmission::$userControlledStatuses))],
            'answers' => ['required', 'array'],
            /*
            'answers.*.question_id' => [
                'required',
                function ($attribute, $value, $fail) {
                    // make sure that the question id's form section id belongs to the given form
                    $answer = $this->request->all()['answers'][explode('.', $attribute)[1]];
                    $sectionIds = FormSection::query()
                        ->where('form_id', (string) $this->formSubmission->form_id)
                        ->pluck('id');
                    $question = FormQuestion::query()
                        ->whereIn('form_section_id', $sectionIds)
                        ->where('id', (int) $answer['question_id'])
                        ->first();
                    if (is_null($question)) {
                        $fail("The selected $attribute is invalid.");
                    }
                },
            ],

            'answers.*.options.*.option_id' => [
                'sometimes',
                function ($attribute, $value, $fail) {
                    $requestData = $this->request->all();

                    $answer = $requestData['answers'][explode('.', $attribute)[1]];
                    $option = FormQuestionOption::query()
                        ->where('id', (int) $value)
                        ->where('form_question_id', (int) $answer['question_id'])
                        ->first();

                    if (is_null($option)) {
                        $fail("The selected $attribute is invalid.");
                    }
                },
            ],
            */
        ];
    }
}
