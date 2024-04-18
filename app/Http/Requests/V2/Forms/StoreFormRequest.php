<?php

namespace App\Http\Requests\V2\Forms;

use App\Models\V2\Forms\Form;
use Illuminate\Foundation\Http\FormRequest;

class StoreFormRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules()
    {
        return [
            'title' => ['required', 'string', 'min:1', 'max:65000'],
            'subtitle' => ['sometimes', 'string', 'min:1', 'max:65000'],
            'framework_key' => ['sometimes', 'string', 'min:1', 'max:250'],
            'type' => ['sometimes', 'string', 'in:' . implode(',', array_keys(Form::$types))],
            'description' => ['sometimes', 'nullable', 'string', 'min:1', 'max:65000'],
            'documentation' => ['sometimes', 'string', 'min:1', 'max:65000'],
            'documentation_label' => ['sometimes', 'string', 'min:1', 'max:250'],
            'deadline_at' => ['sometimes', 'nullable', 'date_format:Y-m-d H:i:s'],
            'submission_message' => ['required', 'string', 'min:1', 'max:65000'],
            'duration' => ['sometimes', 'max:65000'],
            'stage_id' => ['sometimes', 'integer', 'exists:stages,id'],
            'options_other' => ['sometimes', 'boolean'],

            'form_sections' => ['sometimes', 'array'],
            'form_sections.*.order' => ['required', 'integer'],
            'form_sections.*.title' => ['required', 'string', 'max:65000'],
            'form_sections.*.subtitle' => ['sometimes', 'string', 'max:65000'],
            'form_sections.*.description' => ['sometimes', 'nullable', 'max:65000'],

            'form_sections.*.form_questions' => ['sometimes', 'array'],
            'form_sections.*.form_questions.*.linked_field_key' => ['sometimes', 'nullable', 'string'],
            'form_sections.*.form_questions.*.label' => ['required', 'string'],
            'form_sections.*.form_questions.*.input_type' => ['sometimes', 'nullable'],
            'form_sections.*.form_questions.*.name' => ['sometimes', 'nullable'],
            'form_sections.*.form_questions.*.placeholder' => ['sometimes', 'string'],
            'form_sections.*.form_questions.*.description' => ['sometimes'],
            'form_sections.*.form_questions.*.validation' => ['sometimes'],
            'form_sections.*.form_questions.*.multichoice' => ['sometimes'],
            'form_sections.*.form_questions.*.additional_props' => ['sometimes'],
            'form_sections.*.form_questions.*.order' => ['required', 'integer'],
            'form_sections.*.form_questions.*.options_list' => ['sometimes'],

            'form_sections.*.form_questions.*.table_headers' => ['sometimes'],
            'form_sections.*.form_questions.*.table_headers.*.label' => ['sometimes', 'string'],
            'form_sections.*.form_questions.*.table_headers.*.order' => ['sometimes', 'integer'],

            'form_sections.*.form_questions.*.form_question_options' => ['sometimes', 'array'],
            'form_sections.*.form_questions.*.form_question_options.*.slug' => ['sometimes', 'string'],
            'form_sections.*.form_questions.*.form_question_options.*.label' => ['sometimes', 'string'],
            'form_sections.*.form_questions.*.form_question_options.*.image_url' => ['sometimes', 'string'],
            'form_sections.*.form_questions.*.form_question_options.*.order' => ['sometimes', 'integer'],
            'form_sections.*.form_questions.*.form_question_options.*.form_option_list_option_id' => ['sometimes', 'string', 'exists:form_option_list_options,uuid'],

            'form_sections.*.form_questions.*.child_form_questions' => ['sometimes'],
            'form_sections.*.form_questions.*.child_form_questions.*.linked_field_key' => ['sometimes', 'required', 'string'],
            'form_sections.*.form_questions.*.child_form_questions.*.label' => ['sometimes', 'required', 'string'],
            'form_sections.*.form_questions.*.child_form_questions.*.name' => ['sometimes', 'nullable'],
            'form_sections.*.form_questions.*.child_form_questions.*.input_type' => ['sometimes', 'nullable'],
            'form_sections.*.form_questions.*.child_form_questions.*.placeholder' => ['sometimes', 'nullable', 'string'],
            'form_sections.*.form_questions.*.child_form_questions.*.description' => ['sometimes'],
            'form_sections.*.form_questions.*.child_form_questions.*.validation' => ['sometimes'],
            'form_sections.*.form_questions.*.child_form_questions.*.additional_props' => ['sometimes'],
            'form_sections.*.form_questions.*.child_form_questions.*.order' => ['sometimes', 'required', 'integer'],
            'form_sections.*.form_questions.*.child_form_questions.*.options_list' => ['sometimes'],

            'form_sections.*.form_questions.*.child_form_questions.*.table_headers' => ['sometimes'],
            'form_sections.*.form_questions.*.child_form_questions.*.table_headers.*.label' => ['sometimes', 'string'],
            'form_sections.*.form_questions.*.child_form_questions.*.table_headers.*.order' => ['sometimes', 'integer'],
        ];
    }
}
