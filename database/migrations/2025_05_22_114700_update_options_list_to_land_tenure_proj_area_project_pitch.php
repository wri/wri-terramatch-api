<?php

use App\Models\V2\Forms\FormOptionList;
use App\Models\V2\Forms\FormOptionListOption;
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
        //
        $collections = [
            'land-tenure-proj-area-collection' => ['Indigenous Lands', 'Extractive Reserve (RESEX)', 'Sustainable Development Reserve (RDS)', 'National Forest (FLONA)', 'Environmental Protection Area (APA)', 'Rural Settlements (PAE, PAEX, or PDS)', 'Quilombola Lands', 'Undesignated Public Lands', 'Private Lands'],
            'anr-practices-proposed-collection' => ['Fire protection and fighting', 'Livestock management', 'Isolating the area', 'Control of invasive and/or exotic species', 'Maintenance of regenerating individuals', 'Ant control'],
            'territories-of-operation-collection' => ['Indigenous Lands', 'Extractive Reserve (RESEX)', 'Sustainable Development Reserve (RDS)', 'National Forest (FLONA)', 'Environmental Protection Area (APA)', 'Rural Settlements (PAE, PAEX, or PDS)', 'Quilombola Lands', 'Undesignated Public Lands', 'Private Lands'],
            'anr-practices-past-collection' => ['Fire protection and fighting', 'Livestock management', 'Isolating the area', 'Control of invasive and/or exotic species', 'Maintenance of regenerating individuals', 'Ant control'],
            'anr-monitoring-approaches-collection' => ['Count of number of trees regenerating', 'Periodic reports with data on tree planting, growth, survival rate, species diversity, density', 'Comparative analysis of regeneration before and after intervention', 'Livestock management reports.', 'Geospatial monitoring', 'Periodic reports on fence maintenance', 'Photographic records', 'Drone monitoring', 'Comparative analysis of ant colonies'],
        ];

        foreach ($collections as $key => $items) {
            $formOptionList = FormOptionList::create(['key' => $key]);

            foreach ($items as $item) {
                $options = FormOptionListOption::create([
                    'form_option_list_id' => $formOptionList->id,
                    'label' => $item,
                    'slug' => Str::slug($item),
                ]);
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
