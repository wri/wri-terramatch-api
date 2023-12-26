<?php

namespace App\Http\Requests\V2\Forms;

use App\Models\V2\Forms\Form;
use Illuminate\Foundation\Http\FormRequest;

class UpdateFormRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules()
    {
        return [
            'type' => ['sometimes', 'string', 'in:' . implode(',', array_keys(Form::$types))],
            'title' => ['sometimes', 'string', 'min:1', 'max:65000'],
            'subtitle' => ['sometimes', 'string', 'nullable', 'max:65000'],
            'description' => ['sometimes', 'string', 'nullable', 'max:65000'],
            'documentation' => ['sometimes', 'string', 'nullable', 'max:65000'],
            'documentation_label' => ['sometimes', 'string', 'nullable', 'max:250'],
            'deadline_at' => ['sometimes', 'nullable', 'date_format:Y-m-d H:i:s'],
            'submission_message' => ['sometimes', 'string', 'min:1', 'max:65000'],
            'duration' => ['sometimes', 'max:65000'],
            'options_other' => ['sometimes', 'boolean'],

            'form_sections' => ['sometimes', 'array'],            
            'form_sections.*.uuid' => ['sometimes', 'nullable', 'string'],
            'form_sections.*.order' => ['sometimes', 'integer'],
            'form_sections.*.title' => ['sometimes', 'nullable', 'string', 'max:65000'],
            'form_sections.*.subtitle' => ['sometimes', 'nullable', 'string', 'max:65000'],
            'form_sections.*.description' => ['sometimes', 'nullable', 'max:65000'],

            'form_sections.*.form_questions' => ['sometimes', 'nullable', 'array'],
            'form_sections.*.form_questions.*.linked_field_key' => ['sometimes', 'nullable', 'string'],
            'form_sections.*.form_questions.*.label' => ['sometimes', 'nullable', 'string'],
            'form_sections.*.form_questions.*.uuid' => ['sometimes', 'nullable', 'string'],
            'form_sections.*.form_questions.*.name' => ['sometimes', 'nullable'],
            'form_sections.*.form_questions.*.input_type' => ['sometimes', 'nullable'],
            'form_sections.*.form_questions.*.placeholder' => ['sometimes', 'nullable',  'string'],
            'form_sections.*.form_questions.*.description' => ['sometimes', 'nullable', 'string'],
            'form_sections.*.form_questions.*.validation' => ['sometimes', 'nullable'],
            'form_sections.*.form_questions.*.multichoice' => ['sometimes', 'nullable'],
            'form_sections.*.form_questions.*.additional_props' => ['sometimes', 'nullable'],
            'form_sections.*.form_questions.*.order' => ['sometimes', 'nullable', 'integer'],
            'form_sections.*.form_questions.*.options_list' => ['sometimes', 'nullable'],

            'form_sections.*.form_questions.*.form_question_options' => ['sometimes', 'nullable', 'array'],
            'form_sections.*.form_questions.*.form_question_options.*.uuid' => ['sometimes', 'nullable', 'string'],
            'form_sections.*.form_questions.*.form_question_options.*.slug' => ['sometimes', 'nullable', 'string'],
            'form_sections.*.form_questions.*.form_question_options.*.label' => ['sometimes', 'nullable', 'string'],
            'form_sections.*.form_questions.*.form_question_options.*.image_url' => ['sometimes', 'nullable', 'string'],
            'form_sections.*.form_questions.*.form_question_options.*.order' => ['sometimes', 'nullable', 'integer'],
            'form_sections.*.form_questions.*.form_question_options.*.form_option_list_option_id' => ['sometimes', 'nullable', 'string', 'exists:form_option_list_options,uuid'],

            'form_sections.*.form_questions.*.table_headers' => ['sometimes', 'nullable', 'array'],
            'form_sections.*.form_questions.*.table_headers.*.uuid' => ['sometimes', 'nullable', 'string'],
            'form_sections.*.form_questions.*.table_headers.*.label' => ['sometimes', 'nullable', 'string'],
            'form_sections.*.form_questions.*.table_headers.*.order' => ['sometimes', 'nullable', 'integer'],

            'form_sections.*.form_questions.*.child_form_questions' => ['sometimes', 'nullable'],
            'form_sections.*.form_questions.*.child_form_questions.*.uuid' => ['sometimes', 'nullable', 'string'],
            'form_sections.*.form_questions.*.child_form_questions.*.linked_field_key' => ['sometimes', 'nullable', 'string'],
            'form_sections.*.form_questions.*.child_form_questions.*.label' => ['sometimes', 'nullable', 'string'],
            'form_sections.*.form_questions.*.child_form_questions.*.name' => ['sometimes', 'nullable'],
            'form_sections.*.form_questions.*.child_form_questions.*.input_type' => ['sometimes', 'nullable'],
            'form_sections.*.form_questions.*.child_form_questions.*.placeholder' => ['sometimes', 'nullable', 'string'],
            'form_sections.*.form_questions.*.child_form_questions.*.description' => ['sometimes', 'nullable', 'string'],
            'form_sections.*.form_questions.*.child_form_questions.*.validation' => ['sometimes', 'nullable'],
            'form_sections.*.form_questions.*.child_form_questions.*.additional_props' => ['sometimes', 'nullable'],
            'form_sections.*.form_questions.*.child_form_questions.*.order' => ['sometimes', 'integer', 'nullable'],
            'form_sections.*.form_questions.*.child_form_questions.*.options_list' => ['sometimes', 'nullable', 'string'],

            'form_sections.*.form_questions.*.child_form_questions.*.table_headers' => ['sometimes', 'nullable'],
            'form_sections.*.form_questions.*.child_form_questions.*.table_headers.*.uuid' => ['sometimes', 'nullable', 'string'],
            'form_sections.*.form_questions.*.child_form_questions.*.table_headers.*.label' => ['sometimes', 'nullable', 'string'],
            'form_sections.*.form_questions.*.child_form_questions.*.table_headers.*.order' => ['sometimes', 'nullable', 'integer'],

            'form_sections.*.form_questions.*.child_form_questions.*.child_form_questions' => ['sometimes', 'nullable'],
            'form_sections.*.form_questions.*.child_form_questions.*.child_form_questions.*.uuid' => ['sometimes', 'nullable', 'string'],
            'form_sections.*.form_questions.*.child_form_questions.*.child_form_questions.*.linked_field_key' => ['sometimes', 'nullable', 'string'],
            'form_sections.*.form_questions.*.child_form_questions.*.child_form_questions.*.label' => ['sometimes', 'nullable', 'string'],
            'form_sections.*.form_questions.*.child_form_questions.*.child_form_questions.*.name' => ['sometimes', 'nullable'],
            'form_sections.*.form_questions.*.child_form_questions.*.child_form_questions.*.input_type' => ['sometimes', 'nullable'],
            'form_sections.*.form_questions.*.child_form_questions.*.child_form_questions.*.placeholder' => ['sometimes', 'nullable', 'string'],
            'form_sections.*.form_questions.*.child_form_questions.*.child_form_questions.*.description' => ['sometimes', 'nullable', 'string'],
            'form_sections.*.form_questions.*.child_form_questions.*.child_form_questions.*.validation' => ['sometimes', 'nullable'],
            'form_sections.*.form_questions.*.child_form_questions.*.child_form_questions.*.additional_props' => ['sometimes', 'nullable'],
            'form_sections.*.form_questions.*.child_form_questions.*.child_form_questions.*.order' => ['sometimes', 'nullable', 'integer'],
            'form_sections.*.form_questions.*.child_form_questions.*.child_form_questions.*.options_list' => ['sometimes', 'nullable', 'string'],
            'form_sections.*.form_questions.*.child_form_questions.*.child_form_questions.*.table_headers' => ['sometimes', 'nullable'],
            'form_sections.*.form_questions.*.child_form_questions.*.child_form_questions.*.table_headers.*.label' => ['sometimes', 'nullable', 'string'],
            'form_sections.*.form_questions.*.child_form_questions.*.child_form_questions.*.table_headers.*.order' => ['sometimes', 'nullable', 'integer'],
            'form_sections.*.form_questions.*.child_form_questions.*.child_form_questions.*.form_question_options' => ['sometimes', 'nullable', 'array'],
            'form_sections.*.form_questions.*.child_form_questions.*.child_form_questions.*.form_question_options.*.slug' => ['sometimes', 'nullable', 'string'],
            'form_sections.*.form_questions.*.child_form_questions.*.child_form_questions.*.form_question_options.*.label' => ['sometimes', 'nullable', 'string'],
            'form_sections.*.form_questions.*.child_form_questions.*.child_form_questions.*.form_question_options.*.image_url' => ['sometimes', 'nullable', 'string'],
            'form_sections.*.form_questions.*.child_form_questions.*.child_form_questions.*.form_question_options.*.order' => ['sometimes', 'nullable', 'integer'],
            'form_sections.*.form_questions.*.child_form_questions.*.child_form_questions.*.form_question_options.*.form_option_list_option_id' => ['sometimes', 'nullable', 'string', 'exists:form_option_list_options,uuid'],
        ];
    }
}
