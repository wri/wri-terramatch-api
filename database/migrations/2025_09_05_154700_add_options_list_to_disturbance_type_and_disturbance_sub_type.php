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
            'disturbance-types-collection' => ['Climatic', 'Ecological', 'Manmade'],
            'disturbance-subtypes-collection' => ['Flooding', 'Landslide Erosion', 'Drought', 'Fire', 'Heavy Rains', 'Hail', 'Strong Winds', 'Pests Disease', 'Poor Soil', 'Invasive Species', 'Poaching', 'Logging', 'Vandalism', 'Land Use Change Conflict', 'Grazing', 'Mining', 'Lack Community Ownership', 'Cultural Conflict', 'Labor Shortage', 'Inflation', 'Lack Political Will', 'Insecurity'],
            'intensity-collection' => ['Low', 'Medium', 'High'],
            'property-affected-collection' => ['Seedlings', 'Nursery Structure', 'Trees', 'Saplings', 'Animals', 'People', 'Fencing'],
            'extent-collection' => ['0-20', '21-40', '41-60', '61-80', '81-100'],
            'site-affected-collection' => ['Site 1', 'Site 2', 'Site 3'],
            'polygon-affected-collection' => ['Polygon 1', 'Polygon 2', 'Polygon 3'],
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
