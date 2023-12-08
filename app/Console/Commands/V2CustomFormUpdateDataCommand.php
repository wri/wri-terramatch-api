<?php

namespace App\Console\Commands;

use App\Models\V2\Forms\Form;
use App\Models\V2\Forms\FormQuestion;
use App\Models\V2\Forms\FormQuestionOption;
use App\Models\V2\Forms\FormSection;
use App\Models\V2\Forms\FormSubmission;
use App\Models\V2\FundingProgramme;
use App\Models\V2\I18n\I18nItem;
use App\Models\V2\I18n\I18nTranslation;
use App\Models\V2\Stages\Stage;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class V2CustomFormUpdateDataCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'v2-custom-form-update-data {--truncate}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Data setup for custom forms';

    private $currentFundingProgramme;

    protected $fundingNames = ['TerraFund for AFR100: Landscapes - Expression of Interest (Non Profits)', 'TerraFund for AFR100: Landscapes - Expression of Interest (Enterprises)'];

    public function handle()
    {
        if ($this->option('truncate')) {
            $this->clearOldData();
        }

        $this-> setupFundingProgrammes();
        $this-> setupStages();
        $this-> setupCustomForms();

        $this->generateTranslationStubs();
        //        $this->mockTranslations(); // uncomment to generate faked translations locally
    }

    private function generateTranslationStubs(): void
    {
        foreach (FundingProgramme::all() as $fundingProgramme) {
            $fundingProgramme->name_id = $this->generateIfMissingI18nItem($fundingProgramme, 'name');
            $fundingProgramme->description_id = $this->generateIfMissingI18nItem($fundingProgramme, 'description');
            $fundingProgramme->save();
        }

        foreach (Form::all() as $form) {
            $form->title_id = $this->generateIfMissingI18nItem($form, 'title');
            $form->subtitle_id = $this->generateIfMissingI18nItem($form, 'subtitle');
            $form->description_id = $this->generateIfMissingI18nItem($form, 'description');
            $form->save();
        }

        foreach (FormSection::all() as $formSection) {
            $formSection->title_id = $this->generateIfMissingI18nItem($formSection, 'title');
            $formSection->subtitle_id = $this->generateIfMissingI18nItem($formSection, 'subtitle');
            $formSection->description_id = $this->generateIfMissingI18nItem($formSection, 'description');
            $formSection->save();
        }

        foreach (FormQuestion::all() as $formQuestion) {
            $formQuestion->label_id = $this->generateIfMissingI18nItem($formQuestion, 'label');
            $formQuestion->description_id = $this->generateIfMissingI18nItem($formQuestion, 'description');
            $formQuestion->placeholder_id = $this->generateIfMissingI18nItem($formQuestion, 'placeholder');
            $formQuestion->save();
        }

        foreach (FormQuestionOption::all() as $formQuestionOption) {
            $formQuestionOption->label_id = $this->generateIfMissingI18nItem($formQuestionOption, 'label');
            $formQuestionOption->save();
        }
    }

    private function generateIfMissingI18nItem(Model $target, string $property): ?int
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

    private function handleSectionQuestions(FormSection $section, array  $sectionCfg): void
    {
        $i = 0;
        foreach (data_get($sectionCfg, 'fields', []) as $fieldCfg) {
            $i++;
            $formQuestion = FormQuestion::create([
                'form_section_id' => $section->id,
                'linked_field_key' => data_get($fieldCfg, 'linked_field_key', null),
                'input_type' => data_get($fieldCfg, 'input_type'),
                'name' => $this->getValue($fieldCfg, 'name'),
                'label' => $this->getValue($fieldCfg, 'label'),
                'placeholder' => $this->getValue($fieldCfg, 'placeholder'),
                'description' => $this->getValue($fieldCfg, 'description'),
                'validation' => [
                    'required' => data_get($fieldCfg, 'required', true),
                ],
                'multichoice' => data_get($fieldCfg, 'multichoice', false),
                'order' => data_get($fieldCfg, 'order', $i),
                'options_list' => data_get($fieldCfg, 'options_list'),
                'additional_props' => data_get($fieldCfg, 'additional_props'),
                'additional_text' => data_get($fieldCfg, 'additional_text'),
                'additional_url' => data_get($fieldCfg, 'additional_url'),
            ]);

            if (! empty(data_get($fieldCfg, 'options', false))) {
                $this->handleOptions($formQuestion, data_get($fieldCfg, 'options', []));
            }

            if (! empty(data_get($fieldCfg, 'components', false))) {
                $this->handleOptions($formQuestion, data_get($fieldCfg, 'components', []));
            }
        }
    }

    private function handleOptions(FormQuestion $formQuestion, array $optionsCfg): void
    {
        $i = 0;
        foreach ($optionsCfg as $label) {
            $i++;
            FormQuestionOption::create([
                'form_question_id' => $formQuestion->id,
                'label' => $label,
                'order' => $i,
            ]);
        }
    }

    private function setupFormSections(Form $form): void
    {
        $sectionsCfg = config('wri.custom-forms-setup.sections', []);
        foreach ($sectionsCfg as $sectionCfg) {
            $section = FormSection::create([
                'form_id' => $form->uuid,
                'order' => 1,
                'title' => $this->getValue($sectionCfg, 'title'),
                'title_id' => null,
                'subtitle' => $this->getValue($sectionCfg, 'subtitle'),
                'subtitle_id' => null,
                'description' => $this->getValue($sectionCfg, 'description'),
                'description_id' => null,
            ]);

            $this->handleSectionQuestions($section,  $sectionCfg);
        }
    }

    private function setupCustomForms(): void
    {
        foreach (Stage::all() as $stage) {
            if (! empty($stage->fundingProgramme)) {
                $this->currentFundingProgramme = $stage->fundingProgramme;

                if (Str::contains(strtolower($this->currentFundingProgramme->name), 'enterprise')) {
                    $form = Form::create([
                        'stage_id' => $stage->uuid,
                        'title' => 'TerraFund for AFR100: Landscapes - Expression of Interest (Enterprises)',
                        'version' => 0,
                        'subtitle' => 'BEFORE YOU BEGIN, PLEASE READ CAREFULLY.  THIS FORM IS FOR ENTERPRISES ONLY. SELECT OTHER FORM IF YOU ARE A NON PROFIT',
                        'description' => 'BEFORE YOU BEGIN, PLEASE READ CAREFULLY.  THIS FORM IS FOR ENTERPRISES ONLY. SELECT OTHER FORM IF YOU ARE A NON PROFIT<br>' .
                            'TerraFund for AFR100: Landscapes will fund projects based in three African Landscapes: the Ghana Cocoa Belt, the Greater Rusizi Basin of Burundi, ' .
                            'the Democratic Republic of the Congo, and Rwanda, and the Great Rift Valley of Kenya. This Expression of Interest (EOI) will be the first of two application phases. ' .
                            'This form must be submitted by May 5 to be considered for funding. ' .
                            'We will let applicants know if they will be invited to submit a full Request for Proposals (RFP) application on May 15.' .
                            '<br>View more details about the application process and eligibility requirements <a href="https://terramatchsupport.zendesk.com/hc/en-us/categories/13162555518491-TerraFund-for-AFR100-Landscapes-" target="_blank">here</a>.',
                        'documentation' => 'https://wri-rm-wp-production.cube-sites.com/wp-content/uploads/2023/03/Terrafund-RFP-enterprises.pdf',
                        'submission_message' => 'submission_message',
                        'duration' => 'duration',
                        'published' => true,
                    ]);
                } else {
                    $form = Form::create([
                        'stage_id' => $stage->uuid,
                        'title' => 'TerraFund for AFR100: Landscapes - Expression of Interest (Non Profits)',
                        'version' => 0,
                        'subtitle' => 'BEFORE YOU BEGIN, PLEASE READ CAREFULLY: THIS FORM IS FOR NON PROFITS ONLY. SELECT OTHER FORM IF YOU ARE AN ENTERPRISE',
                        'description' => 'BEFORE YOU BEGIN, PLEASE READ CAREFULLY: THIS FORM IS FOR NON PROFITS ONLY. SELECT OTHER FORM IF YOU ARE AN ENTERPRISE<br>' .
                            'TerraFund for AFR100: Landscapes will fund projects based in three African Landscapes: the Ghana Cocoa Belt, the Greater Rusizi Basin of Burundi, ' .
                            'the Democratic Republic of the Congo, and Rwanda, and the Great Rift Valley of Kenya. This Expression of Interest (EOI) will be the first of two application phases. ' .
                            'This form must be submitted by May 5 to be considered for funding. ' .
                            'We will let applicants know if they will be invited to submit a full Request for Proposals (RFP) application on May 15.' .
                            '<br>View more details about the application process and eligibility requirements <a href="https://terramatchsupport.zendesk.com/hc/en-us/categories/13162555518491-TerraFund-for-AFR100-Landscapes-" target="_blank">here</a>.',
                        'documentation' => 'https://wri-rm-wp-production.cube-sites.com/wp-content/uploads/2023/03/Terrafund-RFP-nonprofit.pdf',
                        'submission_message' => 'submission_message',
                        'duration' => 'duration',
                        'published' => true,
                    ]);
                }

                $this->setupFormSections($form);
            }
        }
    }

    private function setupStages(): void
    {
        $fundingProgrammes = FundingProgramme::whereIn('name', $this->fundingNames)->get();
        if (Stage::count() == 0) {
            foreach ($fundingProgrammes as $fundingProgramme) {
                Stage::create([
                    'funding_programme_id' => $fundingProgramme->uuid,
                    'order' => 1,
                ]);
            }
        }
    }

    private function setupFundingProgrammes(): void
    {
        $fundingProgrammes = FundingProgramme::whereIn('name', $this->fundingNames)->get();
        if ($fundingProgrammes->count() == 0) {
            $enterprise = FundingProgramme::create([
                'name' => 'TerraFund for AFR100: Landscapes - Expression of Interest (Enterprises)',
                'status' => FundingProgramme::STATUS_ACTIVE,
                'description' => 'Funding projects based in three African Landscapes: the Ghana Cocoa Belt, the Greater Rusizi Basin of Burundi, ' .
                    'the Democratic Republic of the Congo, and Rwanda, and the Great Rift Valley of Kenya. ' .
                    'This Expression of Interest (EOI) will be the first of two application phases.',
            ]);

            $nonProfit = FundingProgramme::create([
                'name' => 'TerraFund for AFR100: Landscapes - Expression of Interest (Non Profits)',
                'status' => FundingProgramme::STATUS_ACTIVE,
                'description' => 'Funding projects based in three African Landscapes: the Ghana Cocoa Belt, the Greater Rusizi Basin of Burundi, ' .
                    'the Democratic Republic of the Congo, and Rwanda, and the Great Rift Valley of Kenya. ' .
                    'This Expression of Interest (EOI) will be the first of two application phases.',
            ]);

            if ($enterprise->getMedia('cover')->count() == 0) {
                $enterprise->addMedia(public_path('images/AFR100-and-TerraMatch.png'))
                    ->addCustomHeaders([
                        'ACL' => 'public-read',
                    ])
                    ->preservingOriginal()
                    ->toMediaCollection('cover');
            }

            if ($nonProfit->getMedia('cover')->count() == 0) {
                $nonProfit->addMedia(public_path('images/AFR100-and-TerraMatch.png'))
                    ->addCustomHeaders([
                        'ACL' => 'public-read',
                    ])
                    ->preservingOriginal()
                    ->toMediaCollection('cover');
            }
        }
    }

    private function clearOldData()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        FundingProgramme::query()->truncate();
        Stage::query()->truncate();
        Form::query()->truncate();
        FormSection::query()->truncate();
        FormQuestion::query()->truncate();
        FormQuestionOption::query()->truncate();
        FormSubmission::query()->truncate();
        I18nItem::query()->truncate();
        I18nTranslation::query()->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }

    private function getValue($fieldCfg, $prop): ?string
    {
        $value = data_get($fieldCfg, $prop);
        if (! is_array($value)) {
            return $value;
        }

        return Str::contains(strtolower($this->currentFundingProgramme->name), 'enterprise') ? $value['enterprise'] : $value['non-profit'];
    }

    private function mockTranslations(): void
    {
        /** TODO :  remove this once actual translations are done, This is purely for realistic testing purposes */
        $languages = [/*'en_EN',*/
            'fr_FR', 'de_DE', ];
        $i18nItems = I18nItem::where('status', I18nItem::STATUS_DRAFT)->get();

        foreach ($languages as $language) {
            $faker = \Faker\Factory::create($language);

            foreach ($i18nItems as $i18nItem) {
                $charCount = round(strlen($i18nItem->value) * 1.5);
                $mockValue = $faker->realText($charCount > 10 ? $charCount : 10);
                $short = strlen($mockValue) < 256;

                I18nTranslation::create([
                    'i18n_item_id' => $i18nItem->id,
                    'language' => substr($language, 0, 2),
                    'short_value' => $short ? $mockValue : null,
                    'long_value' => ! $short ? $mockValue : null,
                ]);
            }
        }
    }
}
