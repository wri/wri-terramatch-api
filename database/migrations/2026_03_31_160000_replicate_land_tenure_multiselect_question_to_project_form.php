<?php

use App\Models\V2\Forms\Form;
use App\Models\V2\Forms\FormQuestion;
use App\Models\V2\Forms\FormQuestionOption;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

return new class extends Migration {
    /**
     * TM-2862 / land tenure project area: copy the same form_question_options as
     * form_questions.id = 4221 (pro-pit-land-tenure-proj-area / Project Pitch) onto every
     * Project form question that binds v2_projects.land_tenure_project_area
     * (linked_field_key = pro-land-tenure-proj-area).
     *
     * Slug/label set matches fqo.id 21054–21063 when 4221 is present in DB.
     */
    private const SOURCE_FORM_QUESTION_ID = 4221;

    private const TARGET_LINKED_FIELD_KEY = 'pro-land-tenure-proj-area';

    /**
     * Fallback if id 4221 is missing (e.g. empty DB): same canonical options as pitch / TM-2862.
     *
     * @var array<string, string>
     */
    private const FALLBACK_OPTIONS_SLUG_TO_LABEL = [
        'indigenous-land' => 'Indigenous Land',
        'extractive-reserve-resex' => 'Extractive Reserve (RESEX)',
        'sustainable-development-reserve-rds' => 'Sustainable Development Reserve (RDS)',
        'national-forest-flona' => 'National Forest (FLONA)',
        'environmental-protection-area-apa' => 'Environmental Protection Area (APA)',
        'rural-settlements-pae-paex-or-pds' => 'Rural Settlements (PAE, PAEX, or PDS)',
        'quilombola-land' => 'Quilombola Land',
        'public-land' => 'Public Land',
        'private-land' => 'Private Land',
        'other-land' => 'Other Land',
    ];

    public function up(): void
    {
        $count = $this->syncPitchOptionsOntoProjectLandTenureQuestions();

        if ($count === 0) {
            Log::info('sync_land_tenure_project_area_options: skipped (no target questions or nothing to apply).');

            return;
        }

        Log::info("sync_land_tenure_project_area_options: updated {$count} project form question(s).");
    }

    public function down(): void
    {
        // Not reversible without storing previous form_question_options.
    }

    private function syncPitchOptionsOntoProjectLandTenureQuestions(): int
    {
        $source = FormQuestion::query()
            ->with([
                'options' => static function ($q): void {
                    $q->orderBy('order');
                },
                'tableHeaders',
            ])
            ->find(self::SOURCE_FORM_QUESTION_ID);

        $targets = FormQuestion::query()
            ->with(['options', 'tableHeaders'])
            ->where('linked_field_key', self::TARGET_LINKED_FIELD_KEY)
            ->whereExists(function ($q): void {
                $q->selectRaw('1')
                    ->from('form_sections')
                    ->join('forms', 'forms.uuid', '=', 'form_sections.form_id')
                    ->whereColumn('form_sections.id', 'form_questions.form_section_id')
                    ->where('forms.type', Form::TYPE_PROJECT)
                    ->whereNull('forms.deleted_at')
                    ->whereNull('form_sections.deleted_at');
            })
            ->get();

        if ($targets->isEmpty()) {
            return 0;
        }

        $updated = 0;

        foreach ($targets as $target) {
            if ($source !== null) {
                $this->applySourceMetadataFromPitchQuestion($source, $target);
                $this->replaceOptionsFromSourceQuestion($source, $target);
                $this->replaceTableHeadersFromSourceQuestion($source, $target);
            } else {
                Log::warning('sync_land_tenure_project_area_options: form_questions.id 4221 missing; using fallback slug list.');
                $this->replaceOptionsFromFallbackList($target);
            }
            $updated++;
        }

        return $updated;
    }

    private function applySourceMetadataFromPitchQuestion(FormQuestion $source, FormQuestion $target): void
    {
        $target->description = $source->description;
        $target->description_id = $source->description_id;
        $target->validation = $source->validation;
        $target->multichoice = $source->multichoice;
        $target->options_list = $source->options_list;
        $target->options_other = $source->options_other;
        $target->input_type = $source->input_type;
        $target->additional_props = $source->additional_props;
        $target->saveQuietly();
    }

    private function replaceOptionsFromSourceQuestion(FormQuestion $source, FormQuestion $target): void
    {
        foreach ($target->options as $existing) {
            $existing->delete();
        }

        if ($source->options->isEmpty()) {
            $this->seedOptionsFromFallbackList($target);

            return;
        }

        foreach ($source->options as $option) {
            $newOption = $option->replicate();
            $newOption->uuid = Str::uuid()->toString();
            $newOption->form_question_id = $target->id;
            $newOption->save();
        }
    }

    private function replaceOptionsFromFallbackList(FormQuestion $target): void
    {
        foreach ($target->options as $existing) {
            $existing->delete();
        }

        $this->seedOptionsFromFallbackList($target);
    }

    private function seedOptionsFromFallbackList(FormQuestion $target): void
    {
        $order = 0;
        foreach (self::FALLBACK_OPTIONS_SLUG_TO_LABEL as $slug => $label) {
            $order += 1;
            FormQuestionOption::create([
                'form_question_id' => $target->id,
                'order' => $order,
                'slug' => $slug,
                'label' => $label,
            ]);
        }
    }

    private function replaceTableHeadersFromSourceQuestion(FormQuestion $source, FormQuestion $target): void
    {
        foreach ($target->tableHeaders as $existing) {
            $existing->delete();
        }

        foreach ($source->tableHeaders as $header) {
            $newHeader = $header->replicate();
            $newHeader->uuid = Str::uuid()->toString();
            $newHeader->form_question_id = $target->id;
            $newHeader->save();
        }
    }
};
