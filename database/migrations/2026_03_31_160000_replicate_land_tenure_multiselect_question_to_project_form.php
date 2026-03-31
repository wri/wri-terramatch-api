<?php

use App\Models\V2\Forms\Form;
use App\Models\V2\Forms\FormQuestion;
use App\Models\V2\Forms\FormQuestionOption;
use App\Models\V2\Forms\FormSection;
use App\Models\V2\Forms\FormTableHeader;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

return new class () extends Migration {
    /**
     * Copy the land tenure multiselect question onto the target Project form.
     * Reference (staging): source question c6f091dd-2e03-449e-9fe3-eb8d898f2a1e → target Project form 83d140f9-a2b6-4e33-957b-96a4acc57eda, section order 4.
     * Idempotent: skips if source/target missing, target is not a Project form, or clone already exists.
     */
    private const SOURCE_QUESTION_UUID = 'c6f091dd-2e03-449e-9fe3-eb8d898f2a1e';

    private const TARGET_PROJECT_FORM_UUID = '83d140f9-a2b6-4e33-957b-96a4acc57eda';

    private const TARGET_SECTION_ORDER = 4;

    public function up(): void
    {
        $newUuid = $this->replicateQuestionOntoProjectForm();

        if ($newUuid === null) {
            Log::info('replicate_land_tenure_to_project_form: skipped (missing data, non-project form, or duplicate).');

            return;
        }

        Log::info("replicate_land_tenure_to_project_form: created form_questions.uuid = {$newUuid}");
    }

    public function down(): void
    {
        if (! $this->removeClonedQuestionFromProjectForm()) {
            Log::info('replicate_land_tenure_to_project_form down: no matching clone to remove.');
        }
    }

    private function replicateQuestionOntoProjectForm(): ?string
    {
        $source = FormQuestion::query()
            ->with(['options', 'tableHeaders'])
            ->where('uuid', self::SOURCE_QUESTION_UUID)
            ->first();

        if ($source === null) {
            return null;
        }

        $targetForm = Form::query()
            ->where('uuid', self::TARGET_PROJECT_FORM_UUID)
            ->first();

        if ($targetForm === null || $targetForm->type !== Form::TYPE_PROJECT) {
            return null;
        }

        $targetSection = FormSection::query()
            ->where('form_id', $targetForm->uuid)
            ->where('order', self::TARGET_SECTION_ORDER)
            ->first();

        if ($targetSection === null) {
            return null;
        }

        if ($this->targetProjectFormAlreadyHasLinkedField($source->linked_field_key)) {
            return null;
        }

        $newQuestion = $source->replicate();
        $newQuestion->uuid = Str::uuid()->toString();
        $newQuestion->form_section_id = $targetSection->id;
        $newQuestion->parent_id = null;
        $newQuestion->order = $this->nextQuestionOrderInSection($targetSection->id);
        $newQuestion->save();

        foreach ($source->options as $option) {
            $newOption = $option->replicate();
            $newOption->uuid = Str::uuid()->toString();
            $newOption->form_question_id = $newQuestion->id;
            $newOption->save();
        }

        foreach ($source->tableHeaders as $header) {
            $newHeader = $header->replicate();
            $newHeader->uuid = Str::uuid()->toString();
            $newHeader->form_question_id = $newQuestion->id;
            $newHeader->save();
        }

        return $newQuestion->uuid;
    }

    private function targetProjectFormAlreadyHasLinkedField(?string $linkedFieldKey): bool
    {
        if ($linkedFieldKey === null || $linkedFieldKey === '') {
            return false;
        }

        return FormQuestion::query()
            ->where('linked_field_key', $linkedFieldKey)
            ->whereHas('section', function ($q): void {
                $q->where('form_id', self::TARGET_PROJECT_FORM_UUID);
            })
            ->exists();
    }

    private function nextQuestionOrderInSection(int $formSectionId): int
    {
        $max = FormQuestion::query()->where('form_section_id', $formSectionId)->max('order');

        return ((int) $max) + 1;
    }

    private function removeClonedQuestionFromProjectForm(): bool
    {
        $source = FormQuestion::query()->where('uuid', self::SOURCE_QUESTION_UUID)->first();
        if ($source === null) {
            return false;
        }

        $targetForm = Form::query()
            ->where('uuid', self::TARGET_PROJECT_FORM_UUID)
            ->first();

        if ($targetForm === null || $targetForm->type !== Form::TYPE_PROJECT) {
            return false;
        }

        $targetSection = FormSection::query()
            ->where('form_id', $targetForm->uuid)
            ->where('order', self::TARGET_SECTION_ORDER)
            ->first();

        if ($targetSection === null) {
            return false;
        }

        $clone = FormQuestion::query()
            ->where('form_section_id', $targetSection->id)
            ->where('linked_field_key', $source->linked_field_key)
            ->first();

        if ($clone === null) {
            return false;
        }

        foreach ($clone->options as $option) {
            $option->delete();
        }

        foreach ($clone->tableHeaders as $header) {
            $header->delete();
        }

        $clone->delete();

        return true;
    }
};
