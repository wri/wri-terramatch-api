<?php

namespace App\Http\Controllers\V2\Forms;

use App\Http\Controllers\Controller;
use App\Http\Requests\V2\Forms\UpdateFormRequest;
use App\Http\Resources\V2\Forms\FormResource;
use App\Models\V2\Forms\Form;
use App\Models\V2\Forms\FormQuestion;
use App\Models\V2\Forms\FormQuestionOption;
use App\Models\V2\Forms\FormSection;
use App\Models\V2\Forms\FormTableHeader;
use App\Models\V2\I18n\I18nItem;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class UpdateFormController extends Controller
{
    public function __invoke(Form $form, UpdateFormRequest $updateFormRequest): FormResource
    {
        $data = $updateFormRequest->validated();
        $data['updated_by'] = Auth::user()->id;

        $stageForms = Form::query()->where('stage_id', $updateFormRequest->stage_id)->get();
        $data['version'] = $stageForms->count() + 1;

        /** Update the parent form */
        $this->updateForm($data, $form);

        /** Update each of the form's sections */
        foreach (data_get($data, 'form_sections', []) as $formSection) {
            $currentFormSection = $this->updateFormSection($formSection, $form);

            /** Update the form questions in each section */
            foreach (data_get($formSection, 'form_questions', []) as $formQuestion) {
                $this->handleQuestions($formQuestion, $currentFormSection);
            }
        }

        return new FormResource($form);
    }

    private function handleQuestions($formQuestion, $currentFormSection, $parentFormQuestion = null)
    {
        $currentFormQuestion = $this->updateFormQuestion($formQuestion, $currentFormSection, $parentFormQuestion);

        /** Update any form question table headers */
        foreach (data_get($formQuestion, 'table_headers', []) as $formQuestionTableHeader) {
            if (data_get($formQuestion, 'input_type') == 'tableInput') {
                $this->updateFormQuestionTableHeader($formQuestionTableHeader, $currentFormQuestion);
            }
        }

        /** Remove form question options that won't be updated or created */
        $this->removeUnusedFormQuestionOptions($currentFormQuestion, data_get($formQuestion, 'form_question_options', []));

        /** Update any form question options */
        foreach (data_get($formQuestion, 'form_question_options', []) as $formQuestionOption) {
            $this->updateFormQuestionOption($formQuestionOption, $currentFormQuestion);
        }

        foreach (data_get($formQuestion, 'child_form_questions', []) as $childFormQuestion) {
            $this->handleQuestions($childFormQuestion, $currentFormSection, $currentFormQuestion);
        }
    }

    private function updateForm(array $data, Form $form): Form
    {
        if (data_get($data, 'deadline_at')) {
            data_set($data, 'deadline_at', Carbon::createFromFormat('Y-m-d H:i:s', data_get($data, 'deadline_at'), 'EST'));
        }

        $form->update($data);

        $form->title_id = $this->generateI18nItem($form, 'title');
        $form->subtitle_id = $this->generateI18nItem($form, 'subtitle');
        $form->description_id = $this->generateI18nItem($form, 'description');
        $form->save();

        return $form;
    }

    private function updateFormSection(array $formSection, Form $parentForm): FormSection
    {
        $data = array_filter($formSection, fn ($i) => ! is_null($i));
        $data['form_id'] = $parentForm->uuid;


        $formSection = FormSection::updateOrCreate(
            ['uuid' => data_get($data, 'uuid')],
            $data
        );

        $formSection->title_id = $this->generateI18nItem($formSection, 'title');
        $formSection->subtitle_id = $this->generateI18nItem($formSection, 'subtitle');
        $formSection->description_id = $this->generateI18nItem($formSection, 'description');
        $formSection->save();

        return $formSection;
    }

    private function updateFormQuestion(array $formQuestion, FormSection $parentFormSection, FormQuestion $parentQuestion = null): FormQuestion
    {
        $data = array_filter($formQuestion, fn ($i) => ! is_null($i));
        $data['form_section_id'] = data_get($data, 'form_section_id') ?: $parentFormSection->id;
        $data['parent_id'] = data_get($parentQuestion, 'uuid');

        $formQuestion = FormQuestion::updateOrCreate(
            ['uuid' => data_get($data, 'uuid')],
            $data,
        );

        $formQuestion->label_id = $this->generateI18nItem($formQuestion, 'label');
        $formQuestion->description_id = $this->generateI18nItem($formQuestion, 'description');
        $formQuestion->placeholder_id = $this->generateI18nItem($formQuestion, 'placeholder');
        $formQuestion->save();

        return $formQuestion;
    }

    private function updateFormQuestionTableHeader(array $formQuestionTableHeader, FormQuestion $parentQuestion = null): FormTableHeader
    {
        $data = array_filter($formQuestionTableHeader, fn ($i) => ! is_null($i));
        $data['form_question_id'] = data_get($data, 'form_question_id') ?: $parentQuestion->id;

        $tableHeader = FormTableHeader::updateOrCreate(
            ['uuid' => data_get($formQuestionTableHeader, 'uuid')],
            $data
        );

        $tableHeader->label_id = $this->generateI18nItem($tableHeader, 'label');
        $tableHeader->save();

        return $tableHeader;
    }

    private function removeUnusedFormQuestionOptions(FormQuestion $formQuestion, array $usedFormQuestionOptions): void
    {
        FormQuestionOption::query()
            ->where('form_question_id', $formQuestion->id)
            ->whereNotIn('uuid', array_column($usedFormQuestionOptions, 'uuid'))
            ->delete();
    }

    private function updateFormQuestionOption(array $formQuestionOption, FormQuestion $parentQuestion = null): FormQuestionOption
    {
        $data = array_filter($formQuestionOption, fn ($i) => ! is_null($i));
        $data['form_question_id'] = data_get($data, 'form_question_id') ?: $parentQuestion->id;

        $formQuestionOption = FormQuestionOption::updateOrCreate(
            ['uuid' => data_get($data, 'uuid')],
            $data
        );

        $formQuestionOption->label_id = $this->generateI18nItem($formQuestionOption, 'label');
        $formQuestionOption->save();

        return $formQuestionOption;
    }

    private function generateI18nItem(Model $target, string $property): ?int
    {
        $value = trim(data_get($target, $property, false));
        $short = strlen($value) <= 256;
        if ($value && data_get($target, $property . '_id', true)) {
            $i18nItem = I18nItem::create([
                'type' => $short ? 'short' : 'long',
                'status' => I18nItem::STATUS_DRAFT,
                'short_value' => $short ? $value : null,
                'long_value' => $short ? null : $value,
            ]);

            return $i18nItem->id;
        }

        return data_get($target, $property . '_id');
    }
}
