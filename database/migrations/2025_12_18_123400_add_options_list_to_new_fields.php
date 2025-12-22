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
            'restoration-strategies' => ['Tree Planting', 'Assisted Natural Regeneration', 'Direct Seeding'],
            'anr-practices' => ['Fire protection and fighting', 'Livestock management', 'Isolating the area', 'Control of invasive and/or exotic species', 'Maintenance of regenerating individuals', 'Ant control'],
            'siting-strategies' => ['Concentrated', 'Distributed', 'Hybrid'],
            'land-use-systems' => ['Agroforest', 'Grassland', 'Natural Forest', 'Mangrove', 'Peatland', 'Riparian Area or Wetland', 'Silvopasture', 'Urban Forest', 'Woodlot or Plantation'],
            'land-tenures-brazil' => ['Indigenous Lands', 'Extractive Reserve (RESEX)', 'Sustainable Development Reserve (RDS)', 'National Forest (FLONA)', 'Environmental Protection Area (APA)', 'Rural Settlements (PAE, PAEX, or PDS)', 'Quilombola Lands', 'Undesignated Public Lands', 'Private Lands'],
            'project-barriers' => ['financial or economic barriers', 'harmful cultural norms', 'inequities in land and tenure rights'],
        ];

        foreach ($collections as $key => $items) {
            $formOptionList = FormOptionList::create(['key' => $key]);

            foreach ($items as $item) {
                FormOptionListOption::create([
                    'form_option_list_id' => $formOptionList->id,
                    'label' => $item,
                    'slug' => Str::slug($item),
                    'image_url' => '/images/V2/'. $key . '/' . Str::slug($item) . '.png',
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
