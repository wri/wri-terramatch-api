<?php

use App\Models\V2\Forms\FormOptionList;
use App\Models\V2\Forms\FormOptionListOption;
use App\Models\V2\Forms\FormQuestion;
use App\Models\V2\Forms\FormQuestionOption;
use App\Models\V2\I18n\I18nItem;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Str;

return new class () extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $linkedFields = [
            'land-use-systems' => [
                'linked_field_key' => ['site-land-use-types', 'pro-land-use-types', 'pro-pit-rst-inv-types', 'pro-pit-land-systems', 'pro-pit-land-use-types', 'pro-pit-land-use-type-distribution', 'org-land-systems'],
                'new_options' => ['open-natural-ecosystem'],
            ],
            'land-tenures' => [
                'linked_field_key' => ['pro-land-tenure-proj-area', 'site-land-tenures'],
                'new_options' => ['state-land'],
            ],
        ];

        foreach ($linkedFields as $key => $items) {
            $formOptionList = FormOptionList::where('key', $key)->first();

            if (! $formOptionList) {
                continue;
            }

            foreach ($items['new_options'] as $newOption) {
                FormOptionListOption::firstOrCreate([
                    'form_option_list_id' => $formOptionList->id,
                    'label' => $newOption,
                    'slug' => Str::slug($newOption),
                ]);

                $formQuestionIds = FormQuestion::whereIn('linked_field_key', $items['linked_field_key'])->pluck('id');
                foreach ($formQuestionIds as $formQuestionId) {
                    FormQuestionOption::firstOrCreate([
                        'form_question_id' => $formQuestionId,
                        'label' => $newOption,
                        'slug' => Str::slug($newOption),
                    ]);
                }
            }


        }

        foreach (FormOptionListOption::all() as $option) {
            $option->label_id = $this->generateIfMissingI18nItem($option, 'label');
            $option->save();
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
};
