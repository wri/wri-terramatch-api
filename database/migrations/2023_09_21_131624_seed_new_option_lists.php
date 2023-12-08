<?php

use App\Models\V2\Forms\FormOptionList;
use App\Models\V2\Forms\FormOptionListOption;
use App\Models\V2\I18n\I18nItem;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Str;

class SeedNewOptionLists extends Migration
{
    /**
     * This migration seeds the default options
     *
     * @return void
     */
    public function up()
    {
        $collections = [
            'yes-no' => ['No', 'Yes'],
            'board-remuneration' => ['No', 'Yes, 1-20% of board members', 'Yes, 20-30% of board members', 'Yes, 40-50% of board members', 'Yes, more than 50% of board members'],
            'board-engagement' => ['Never', 'Yes, sometimes', 'Yes, always'],
            'biodiversity' => ['Human-wildlife coexistence', 'Preservation or regeneration of local flora and fauna', 'Interventions along wildlife corridors', 'Monitoring of indicator species and/or genetic variability', 'Removal of alien/invasive species', 'Grassland restoration' ],
            'planning-frameworks' => ['Global Biodiversity Standard', 'Species Threat Abatement Restoration Metric (STAR)', 'Landscape Assessment Framework', 'Global Ecosystem Restoration Index', 'International principles and standards for the practice of ecological restoration. Second edition', 'Ten people-centered rules for socially sustainable ecosystem restoration'],
            'engagement-landless' => ['We provide paid jobs for landless people','We directly engage and benefit landless people', 'We provide indirect benefits to women', 'We do not engage with landless people' ],
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
}
