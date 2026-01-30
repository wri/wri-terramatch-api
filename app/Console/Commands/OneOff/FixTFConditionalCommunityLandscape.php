<?php

namespace App\Console\Commands\OneOff;

use App\Models\V2\Projects\ProjectReport;
use Illuminate\Console\Command;

class FixTFConditionalCommunityLandscape extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'one-off:fix-tf-conditional-community-landscape';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'update conditional question to landscape_community_contribution field';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting TerraFund reports fix…');
        ProjectReport::where('framework_key', 'terrafund')
            ->where('status', 'approved')
            ->chunk(100, function ($reports) {
                foreach ($reports as $report) {
                    $form = $report->getForm();
                    $formConfig = $report->getFormConfig();
                    $fieldsConfig = data_get($formConfig, 'fields', []);
                    $formData = $report->getEntityAnswers($report->getForm());

                    $entityProps = [];

                    foreach ($form->sections as $section) {
                        foreach ($section->questions as $question) {
                            $value = data_get($formData, $question->uuid, null);
                            if ($question->linked_field_key === 'pro-rep-landscape-com-con') {
                                $parentValue = data_get($formData, $question->parent_id);
                                $value = data_get($formData, $question->uuid);

                                if (! empty($question->parent_id) && $parentValue === true && ! is_null($value)) {
                                    $fieldConfig = data_get($fieldsConfig, $question->linked_field_key);
                                    $property = data_get($fieldConfig, 'property');

                                    if (! empty($property)) {
                                        $entityProps[$property] = '';
                                    }
                                }
                            }
                        }
                    }

                    if (! empty($entityProps)) {
                        $report->update($entityProps);
                    }
                }
            });

        $this->info('Finished updating landscape_community_contribution field.');
    }
}
