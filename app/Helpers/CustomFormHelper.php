<?php

namespace App\Helpers;

use App\Models\V2\Forms\Form;
use App\Models\V2\Forms\FormQuestion;
use App\Models\V2\Forms\FormSection;
use App\Models\V2\Nurseries\Nursery;
use App\Models\V2\Nurseries\NurseryReport;
use App\Models\V2\Projects\Project;
use App\Models\V2\Projects\ProjectReport;
use App\Models\V2\Sites\Site;
use App\Models\V2\Sites\SiteReport;

class CustomFormHelper
{
    /**
     * Used in unit tests to fake up realistic forms based on the linked fields config
     *
     * @param string $type [project,site,nursery,project-report,site-report,nursery-report]
     * @param string $framework [ppc,terrafund]
     * @return Form
     */
    public static function generateFakeForm(string $type, string $framework): Form
    {
        switch ($type) {
            case 'project':
                $model = Project::class;

                break;
            case 'site':
                $model = Site::class;

                break;
            case 'nursery':
                $model = Nursery::class;

                break;
            case 'project-report':
                $model = ProjectReport::class;

                break;
            case 'site-report':
                $model = SiteReport::class;

                break;
            case 'nursery-report':
                $model = NurseryReport::class;

                break;
        }

        $form = Form::where(['framework_key' => $framework, 'model' => $model])->first();
        if ($form != null) {
            // If we've already generated a fake form for this combo, use it because otherwise the form the
            // controller gets from the DB will be different from the form in use by the test.
            return $form;
        }

        $form = Form::factory()->create(['framework_key' => $framework, 'model' => $model]);
        $section = FormSection::factory()->create(['form_id' => $form->uuid]);
        foreach (config('wri.linked-fields.models.' . $type . '.fields') as $key => $fieldCfg) {
            FormQuestion::factory()->create(
                [
                    'input_type' => data_get($fieldCfg, 'input_type'),
                    'label' => data_get($fieldCfg, 'label'),
                    'name' => data_get($fieldCfg, 'label'),
                    'form_section_id' => $section->id,
                    'linked_field_key' => $key,
                ]
            );
        }

        $conditional = FormQuestion::factory()->conditionalField()->create(['form_section_id' => $section->id]);
        FormQuestion::factory()->create([
            'form_section_id' => $section->id,
            'parent_id' => $conditional->uuid,
            'show_on_parent_condition' => false,
        ]);

        return $form;
    }
}
