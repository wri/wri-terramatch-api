<?php

namespace App\Console\Commands;

use App\Models\V2\Forms\Form;
use App\Models\V2\Forms\FormQuestion;
use App\Models\V2\Forms\FormQuestionOption;
use App\Models\V2\Forms\FormSection;
use App\Models\V2\Forms\FormTableHeader;
use App\Models\V2\FundingProgramme;
use App\Models\V2\I18n\I18nItem;
use App\Models\V2\Stages\Stage;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class V2CustomFormRFPUpdateDataCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'v2-custom-form-rfp-update-data {--fresh}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Data setup for RFP custom forms';

    private $fundingKey;

    protected $fundingNames = [
        'TerraFund for AFR100: Landscapes - Expression of Interest (Non Profits)',
        'TerraFund for AFR100: Landscapes - Expression of Interest (Enterprises)',
    ];

    private $forms = [
        [
            'uuid' => 'd73b360f-87c6-48cb-ad66-3be6b32aef5e',
            'name' => 'TerraFund for AFR100: Landscapes - Request for Proposals (Non Profits)',
        ],
        [
            'uuid' => '41e2fedc-ed7f-4bb4-9745-43fb7440967a',
            'name' => 'TerraFund for AFR100: Landscapes - Request for Proposals (Enterprises)',
        ],
    ];

    public function handle()
    {
        if ($this->option('fresh')) {
            $this->clearOldDataById();
        }

        $this-> setupNonProfitRFP();
        $this-> setupEnterpriseRFP();
        $this->generateTranslationStubs();
    }

    private function handleSectionQuestions(FormSection $section, array  $sectionCfg): void
    {
        $i = 0;
        foreach (data_get($sectionCfg, 'fields', []) as $fieldCfg) {
            $i++;
            if (empty(data_get($fieldCfg, 'only', null)) || $this->fundingKey === data_get($fieldCfg, 'only', null)) {
                if (data_get($fieldCfg, 'children', false)) {
                    $c = 1;
                    $parent = $this->createQuestion($section, $fieldCfg, $i);

                    foreach (data_get($fieldCfg, 'children') as $cfgChild) {
                        $formQuestion = $this->createQuestion($section, $cfgChild, $c++, $parent);
                    }
                } else {
                    $formQuestion = $this->createQuestion($section, $fieldCfg, $i);
                }
            }
        }
    }

    private function createQuestion(FormSection $section, array $fieldCfg, int $order, FormQuestion $parent = null): FormQuestion
    {
        $formQuestion = FormQuestion::create([
            'form_section_id' => $section->id,
            'parent_id' => $parent ? $parent->uuid : null,
            'linked_field_key' => data_get($fieldCfg, 'linked_field_key', null),
            'input_type' => data_get($fieldCfg, 'input_type'),
            'name' => $this->getValue($fieldCfg, 'name'),
            'label' => $this->getValue($fieldCfg, 'label'),
            'placeholder' => $this->getValue($fieldCfg, 'placeholder'),
            'description' => $this->getValue($fieldCfg, 'description'),
            'validation' => $this->getValue($fieldCfg, 'validation', json_encode(['required' => true])),
            'multichoice' => data_get($fieldCfg, 'multichoice', false),
            'order' => data_get($fieldCfg, 'order', $order),
            'options_list' => data_get($fieldCfg, 'options_list'),
            'additional_props' => data_get($fieldCfg, 'additional_props'),
            'additional_text' => data_get($fieldCfg, 'additional_text'),
            'additional_url' => data_get($fieldCfg, 'additional_url'),
        ]);

        if (! empty(data_get($fieldCfg, 'options', false))) {
            $this->handleOptions($formQuestion, data_get($fieldCfg, 'options', []));
        }

        if (! empty(data_get($fieldCfg, 'table_headers', false))) {
            $this->handleTableHeaders($formQuestion, data_get($fieldCfg, 'table_headers', []));
        }

        return $formQuestion;
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

    private function handleTableHeaders(FormQuestion $formQuestion, array $tableHeadersCfg): void
    {
        $i = 0;
        foreach ($tableHeadersCfg as $label) {
            $i++;
            FormTableHeader::create([
                'form_question_id' => $formQuestion->id,
                'label' => $label,
                'order' => $i,
            ]);
        }
    }

    private function setupFormSections(Form $form): void
    {
        $sectionsCfg = config('wri.rfp-forms-setup.sections', []);
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

    private function setupNonProfitRFP(): void
    {
        $fundingProgrammeNonProfit = FundingProgramme::where('name', $this->fundingNames[0])->first();
        $this->fundingKey = 'non-profit';
        $stage = Stage::create([
            'funding_programme_id' => $fundingProgrammeNonProfit->uuid,
            'name' => 'Request Funding Programme (Non Profits)',
            'order' => 2,
            'status' => Stage::STATUS_ACTIVE,
        ]);

        $formNonProfit = Form::create([
            'stage_id' => $stage->uuid,
            'title' => 'TerraFund for AFR100: Landscapes - Request for Proposals (Non Profits)',
            'version' => 0,
            'subtitle' => 'BEFORE YOU BEGIN, PLEASE READ CAREFULLY: THIS FORM IS FOR NON PROFITS ONLY. SELECT OTHER FORM IF YOU ARE AN ENTERPRISE.',
            'description' => 'BEFORE YOU BEGIN, PLEASE READ CAREFULLY: THIS FORM IS FOR NON PROFITS ONLY. SELECT OTHER FORM IF YOU ARE AN ENTERPRISE.<br>' .
                'TerraFund for AFR100: Landscapes will fund projects based in three African Landscapes: the Ghana Cocoa Belt, the Greater Rusizi Basin of Burundi, ' .
                'the Democratic Republic of the Congo, and Rwanda, and the Great Rift Valley of Kenya. This Request for Proposals (RFP) will be the second of ' .
                'two application phases. This form must be submitted by June 16 to be considered for funding. We will let applicants know if they will be invited ' .
                'to Risk Review and Selection Interviews on August 7-25 2023.<br>' .
                'View more details about the applicaiton process ' .
                '<a href="https://terramatchsupport.zendesk.com/hc/en-us/categories/13162555518491-TerraFund-for-AFR100-Landscapes-" target="_blank">here</a>',
            'documentation' => 'https://wri-rm-wp-production.cube-sites.com/wp-content/uploads/2023/03/Terrafund-RFP-enterprises.pdf',
            'submission_message' => 'submission_message',
            'duration' => 'duration',
            'published' => true,
        ]);
        $this->setupFormSections($formNonProfit);
    }

    private function setupEnterpriseRFP(): void
    {
        $fundingProgrammeEnterprise = FundingProgramme::where('name', $this->fundingNames[1])->first();
        $this->fundingKey = 'enterprise';
        $stage = Stage::create([
            'funding_programme_id' => $fundingProgrammeEnterprise->uuid,
            'name' => 'Request Funding Programme (Enterprise)',
            'order' => 2,
            'status' => Stage::STATUS_ACTIVE,
        ]);

        $formEnterprise = Form::create([
            'stage_id' => $stage->uuid,
            'title' => 'TerraFund for AFR100: Landscapes - Request for Proposals (Enterprise)',
            'version' => 0,
            'subtitle' => 'BEFORE YOU BEGIN, PLEASE READ CAREFULLY: THIS FORM IS FOR ENTERPRISES ONLY. SELECT OTHER FORM IF YOU ARE A NON PROFIT',
            'description' => 'BEFORE YOU BEGIN, PLEASE READ CAREFULLY: THIS FORM IS FOR ENTERPRISES ONLY. SELECT OTHER FORM IF YOU ARE A NON PROFIT<br>' .
                'TerraFund for AFR100: Landscapes will fund projects based in three African Landscapes: the Ghana Cocoa Belt, the Greater Rusizi Basin of Burundi, ' .
                'the Democratic Republic of the Congo, and Rwanda, and the Great Rift Valley of Kenya. This Request for Proposals (RFP) will be the second of ' .
                'two application phases. This form must be submitted by June 16 to be considered for funding. We will let applicants know if they will be invited ' .
                'to Risk Review and Selection Interviews on August 7-25 2023.<br>'.
                'View more details about the applicaiton process '.
                '<a href="https://terramatchsupport.zendesk.com/hc/en-us/categories/13162555518491-TerraFund-for-AFR100-Landscapes-" target="_blank">here</a>',
            'documentation' => 'https://wri-rm-wp-production.cube-sites.com/wp-content/uploads/2023/03/Terrafund-RFP-enterprises.pdf',
            'submission_message' => 'submission_message',
            'duration' => 'duration',
            'published' => true,
        ]);
        $this->setupFormSections($formEnterprise);
    }

    private function clearOldDataById()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        $this->clearAndResetIndexById('stages', 3);
        $this->clearAndResetIndexById('forms', 3);
        $this->clearAndResetIndexById('form_sections', 13);
        $this->clearAndResetIndexById('form_questions', 73);
        $this->clearAndResetIndexById('form_question_options', 111);
        $this->clearAndResetIndexById('i18n_items', 277);
        FormTableHeader::query()->truncate();

        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }

    private function clearAndResetIndexById(string $table, int $id)
    {
        $statement = "DELETE FROM $table WHERE id >= $id ;";
        DB::unprepared($statement);

        $statement = "ALTER TABLE $table AUTO_INCREMENT = $id;";
        DB::unprepared($statement);
    }

    private function getValue($fieldCfg, $prop): ?string
    {
        $value = data_get($fieldCfg, $prop);
        if (! is_array($value)) {
            return $value;
        }

        return $value[$this->fundingKey] ;
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

        foreach (FormTableHeader::all() as $formQuestionOption) {
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
}
