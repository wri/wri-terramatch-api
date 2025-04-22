<?php

namespace App\Http\Controllers\V2\Forms;

use App\Helpers\I18nHelper;
use App\Helpers\UploadHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\V2\Forms\StoreFormRequest;
use App\Http\Resources\V2\Forms\FormResource;
use App\Models\V2\Forms\Form;
use App\Models\V2\Forms\FormQuestion;
use App\Models\V2\Forms\FormQuestionOption;
use App\Models\V2\Forms\FormSection;
use App\Models\V2\Forms\FormTableHeader;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class StoreFormController extends Controller
{
    public function __invoke(StoreFormRequest $storeFormRequest): FormResource
    {
        $data = $storeFormRequest->validated();
        $data['updated_by'] = Auth::user()->id;
        if (isset($data['document'])) {
            $data['document'] = UploadHelper::findByIdAndValidate(
                $data['document'],
                UploadHelper::FILES_DOC_PDF,
                Auth::user()->id,
            );
        }

        $stageForms = Form::query()->where('stage_id', $storeFormRequest->stage_id)->get();
        $data['version'] = $stageForms->count() + 1;

        /** Create the parent form */
        $form = $this->createForm($data);

        /** Create each of the form's sections */
        foreach (data_get($data, 'form_sections', []) as $formSection) {
            $currentFormSection = $this->createFormSection($formSection, $form);

            /** Create the form questions in each section */
            foreach (data_get($formSection, 'form_questions', []) as $formQuestion) {
                $this->handleQuestions($formQuestion, $currentFormSection);
            }
        }

        return new FormResource($form);
    }

    private function handleQuestions($formQuestion, $currentFormSection, $parentFormQuestion = null)
    {
        $currentFormQuestion = $this->createFormQuestion($formQuestion, $currentFormSection, $parentFormQuestion);

        /** Create any form question table headers */
        foreach (data_get($formQuestion, 'table_headers', []) as $formQuestionTableHeader) {
            if (data_get($formQuestion, 'input_type') == 'tableInput') {
                $this->createFormQuestionTableHeader($formQuestionTableHeader, $currentFormQuestion);
            }
        }

        /** Create any form question options */
        foreach (data_get($formQuestion, 'form_question_options', []) as $formQuestionOption) {
            $this->createFormQuestionOption($formQuestionOption, $currentFormQuestion);
        }

        foreach (data_get($formQuestion, 'child_form_questions', []) as $childFormQuestion) {
            $this->handleQuestions($childFormQuestion, $currentFormSection, $currentFormQuestion);
        }
    }

    private function createForm(array $data): Form
    {
        $form = Form::create([
            'type' => data_get($data, 'type'),
            'title' => data_get($data, 'title'),
            'subtitle' => data_get($data, 'subtitle'),
            'framework_key' => data_get($data, 'framework_key'),
            'description' => data_get($data, 'description'),
            'duration' => data_get($data, 'duration'),
            'documentation' => data_get($data, 'documentation'),
            'documentation_label' => data_get($data, 'documentation_label'),
            'deadline_at' => ! is_null(data_get($data, 'deadline_at')) ? Carbon::createFromFormat('Y-m-d H:i:s', data_get($data, 'deadline_at'), 'EST') : null,
            'document' => data_get($data, 'document'),
            'submission_message' => data_get($data, 'submission_message'),
            'published' => data_get($data, 'published'),
            'stage_id' => data_get($data, 'stage_id'),
            'updated_by' => data_get($data, 'updated_by'),
            'version' => data_get($data, 'version'),
            'published' => false,
        ]);

        $form->title_id = I18nHelper::generateI18nItem($form, 'title');
        $form->subtitle_id = I18nHelper::generateI18nItem($form, 'subtitle');
        $form->description_id = I18nHelper::generateI18nItem($form, 'description');
        $form->save();

        return $form;
    }

    private function createFormSection(array $formSection, Form $parentForm): FormSection
    {
        $formSection = FormSection::create([
            'order' => data_get($formSection, 'order'),
            'title' => data_get($formSection, 'title'),
            'subtitle' => data_get($formSection, 'subtitle'),
            'description' => data_get($formSection, 'description'),
            'form_id' => $parentForm->uuid,
        ]);

        $formSection->title_id = I18nHelper::generateI18nItem($formSection, 'title');
        $formSection->subtitle_id = I18nHelper::generateI18nItem($formSection, 'subtitle');
        $formSection->description_id = I18nHelper::generateI18nItem($formSection, 'description');
        $formSection->save();

        return $formSection;
    }

    private function createFormQuestion(array $formQuestion, FormSection $parentFormSection, FormQuestion $parentQuestion = null): FormQuestion
    {
        $formQuestion = FormQuestion::create([
            'linked_field_key' => data_get($formQuestion, 'linked_field_key'),
            'input_type' => data_get($formQuestion, 'input_type', 'text'),
            'label' => data_get($formQuestion, 'label'),
            'name' => data_get($formQuestion, 'name'),
            'placeholder' => data_get($formQuestion, 'placeholder'),
            'description' => data_get($formQuestion, 'description'),
            'validation' => data_get($formQuestion, 'validation'),
            'additional_props' => data_get($formQuestion, 'additional_props'),
            'order' => data_get($formQuestion, 'order'),
            'options_other' => data_get($formQuestion, 'options_other', false),
            'multichoice' => data_get($formQuestion, 'multichoice', false),
            'collection' => data_get($formQuestion, 'collection', false),
            'options_list' => data_get($formQuestion, 'options_list', false),
            'parent_id' => data_get($parentQuestion, 'uuid'),
            'show_on_parent_condition' => data_get($formQuestion, 'show_on_parent_condition'),
            'form_section_id' => data_get($parentFormSection, 'id'),
            'min_character_limit' => data_get($formQuestion, 'min_character_limit'),
            'max_character_limit' => data_get($formQuestion, 'max_character_limit'),
            'years' => data_get($formQuestion, 'years'),
            'min_number_limit' => data_get($formQuestion, 'min_number_limit'),
            'max_number_limit' => data_get($formQuestion, 'max_number_limit'),
        ]);

        $formQuestion->label_id = I18nHelper::generateI18nItem($formQuestion, 'label');
        $formQuestion->description_id = I18nHelper::generateI18nItem($formQuestion, 'description');
        $formQuestion->placeholder_id = I18nHelper::generateI18nItem($formQuestion, 'placeholder');
        $formQuestion->save();

        return $formQuestion;
    }

    private function createFormQuestionTableHeader(array $formQuestionTableHeader, FormQuestion $parentQuestion = null): FormTableHeader
    {
        $tableHeader = FormTableHeader::create([
            'label' => data_get($formQuestionTableHeader, 'label', 'Header'),
            'order' => data_get($formQuestionTableHeader, 'order'),
            'form_question_id' => data_get($parentQuestion, 'id'),
        ]);

        $tableHeader->label_id = I18nHelper::generateI18nItem($tableHeader, 'label');
        $tableHeader->save();

        return $tableHeader;
    }

    private function createFormQuestionOption(array $formQuestionOption, FormQuestion $parentQuestion = null): FormQuestionOption
    {
        $formQuestionOption = FormQuestionOption::create([
            'slug' => data_get($formQuestionOption, 'slug'),
            'label' => data_get($formQuestionOption, 'label'),
            'image_url' => data_get($formQuestionOption, 'image_url'),
            'order' => data_get($formQuestionOption, 'order'),
            'form_option_list_option_id' => data_get($formQuestionOption, 'form_option_list_option_id'),
            'form_question_id' => data_get($parentQuestion, 'id'),
        ]);

        $formQuestionOption->label_id = I18nHelper::generateI18nItem($formQuestionOption, 'label');
        $formQuestionOption->save();

        return $formQuestionOption;
    }
}
