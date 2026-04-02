<?php

use App\Models\V2\Forms\Form;
use App\Models\V2\Forms\FormOptionList;
use App\Models\V2\Forms\FormOptionListOption;
use App\Models\V2\Forms\FormQuestion;
use App\Models\V2\Forms\FormQuestionOption;
use App\Models\V2\I18n\I18nItem;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

return new class extends Migration {
    /**
     * Fundo Flora: align Site form land tenure (site-land-tenures / v2_sites.land_tenures)
     * with Project Pitch question options (form_questions.id 4221, pro-pit-land-tenure-proj-area).
     *
     * Project profile land tenure (pro-land-tenure-proj-area) is handled by
     * 2026_03_31_160000_replicate_land_tenure_multiselect_question_to_project_form.
     *
     * Also seeds pro-landowner-agreement form options and landowner-collection list options.
     */
    private const SOURCE_FORM_QUESTION_ID = 4221;

    private const SITE_LAND_TENURE_LINKED_FIELD_KEY = 'site-land-tenures';

    private const LANDOWNER_AGREEMENT_LINKED_FIELD_KEY = 'pro-landowner-agreement';

    private const LANDOWNER_OPTION_LIST_KEY = 'landowner-collection';

    /**
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

    /**
     * @var array<string, string>
     */
    private const LANDOWNER_AGREEMENT_SLUG_TO_LABEL = [
        'yes-the-organization-has-a-document-that-proves-authorization' => 'Yes, the organization has a document that proves authorization',
        'yes-the-organization-has-a-verbal-agreement' => 'Yes, the organization has a verbal agreement',
        'no-the-organization-has-not-yet-secured-a-prior-agreement-andor-a-document-proving-authorization' => 'No, the organization has not yet secured a prior agreement and/or a document proving authorization',
        'not-applicable-the-organization-is-the-legal-occupant-of-the-land' => 'Not applicable – the organization is the legal occupant of the land',
        'not-applicable-i-represent-an-organizationassociationcommunity-that-is-the-legal-occupant-or-landowner' => 'Not applicable – I represent an organization/association/community that is the legal occupant or landowner',
    ];

    public function up(): void
    {
        DB::transaction(function (): void {
            $siteCount = $this->syncPitchOptionsOntoSiteLandTenureQuestions();
            if ($siteCount === 0) {
                Log::info('sync_site_land_tenure_options: skipped (no target questions or nothing to apply).');
            } else {
                Log::info("sync_site_land_tenure_options: updated {$siteCount} site form question(s).");
            }

            $landownerCount = $this->seedLandownerAgreementOptions();
            Log::info("seed_landowner_agreement_options: touched {$landownerCount} form question(s) and option list.");
        });
    }

    public function down(): void
    {
        // Not reversible without storing previous form_question_options / list options.
    }

    private function syncPitchOptionsOntoSiteLandTenureQuestions(): int
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
            ->where('linked_field_key', self::SITE_LAND_TENURE_LINKED_FIELD_KEY)
            ->whereExists(function ($q): void {
                $q->selectRaw('1')
                    ->from('form_sections')
                    ->join('forms', 'forms.uuid', '=', 'form_sections.form_id')
                    ->whereColumn('form_sections.id', 'form_questions.form_section_id')
                    ->where('forms.type', Form::TYPE_SITE)
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
                Log::warning('sync_site_land_tenure_options: form_questions.id 4221 missing; using fallback slug list.');
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
            $questionOption = FormQuestionOption::create([
                'form_question_id' => $target->id,
                'order' => $order,
                'slug' => $slug,
                'label' => $label,
            ]);
            if (empty($questionOption->label_id)) {
                $questionOption->label_id = $this->generateIfMissingI18nItem($questionOption, 'label');
                $questionOption->save();
            }
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

    private function seedLandownerAgreementOptions(): int
    {
        $optionList = FormOptionList::where('key', self::LANDOWNER_OPTION_LIST_KEY)->first();
        if ($optionList !== null) {
            foreach (self::LANDOWNER_AGREEMENT_SLUG_TO_LABEL as $slug => $label) {
                $listOption = FormOptionListOption::firstOrCreate(
                    [
                        'form_option_list_id' => $optionList->id,
                        'slug' => $slug,
                    ],
                    [
                        'label' => $label,
                    ]
                );
                if (empty($listOption->label_id)) {
                    $listOption->label_id = $this->generateIfMissingI18nItem($listOption, 'label');
                    $listOption->save();
                }
            }
        }

        $questions = FormQuestion::query()
            ->where('linked_field_key', self::LANDOWNER_AGREEMENT_LINKED_FIELD_KEY)
            ->get();

        foreach ($questions as $question) {
            $questionId = (int) $question->id;
            $nextOrder = (int) FormQuestionOption::where('form_question_id', $questionId)->max('order');

            foreach (self::LANDOWNER_AGREEMENT_SLUG_TO_LABEL as $slug => $label) {
                $existing = FormQuestionOption::where('form_question_id', $questionId)
                    ->where('slug', $slug)
                    ->first();

                if ($existing !== null) {
                    if (empty($existing->label_id)) {
                        $existing->label_id = $this->generateIfMissingI18nItem($existing, 'label');
                        $existing->save();
                    }

                    continue;
                }

                $nextOrder++;
                $questionOption = FormQuestionOption::create([
                    'form_question_id' => $questionId,
                    'order' => $nextOrder,
                    'slug' => $slug,
                    'label' => $label,
                ]);
                if (empty($questionOption->label_id)) {
                    $questionOption->label_id = $this->generateIfMissingI18nItem($questionOption, 'label');
                    $questionOption->save();
                }
            }
        }

        return $questions->count();
    }

    private function generateIfMissingI18nItem(Model $target, string $property): ?int
    {
        $value = trim((string) data_get($target, $property, ''));
        $short = strlen($value) <= 256;

        if ($value !== '' && empty(data_get($target, $property . '_id'))) {
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
};
