<?php

use App\Models\V2\Forms\FormOptionList;
use App\Models\V2\Forms\FormOptionListOption;
use App\Models\V2\I18n\I18nItem;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Migrations\Migration;

return new class () extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $key = 'income-generating-activities';

        if (! FormOptionList::where('key', $key)->exists()) {
            $formOptionList = FormOptionList::create(['key' => $key]);

            $items = [
                'oil-processing' => 'Establishing oil processing centers such as palm oil or avocado',
                'small-animals' => 'Supporting farmers with small animals such as poultry or rabbit farming',
                'soil-water-conservation' => 'Promoting soil and water conservation practices',
                'home-gardens' => 'Establishing home gardens',
                'farmer-field-schools' => 'Establishing farmer field schools',
                'market-linkages' => 'Establishing market linkages',
                'cookstoves' => 'Promoting energy saving cookstoves such as use of briquettes',
                'high-value-crops' => 'Promoting high value crops',
                'fruits-vegetables' => 'Promoting fruit and vegetables',
                'climate-smart-agriculture' => 'Supporting climate smart agriculture',
                'cover-crops' => 'Promoting cover crops, fodder crops or intercropping',
                'training-smallholder-farmers' => 'Trainings to smallholder farmers on restoration',
                'savings-loans' => 'Establishing village savings and loans associations or local cooperatives',
                'beekeeping' => 'Beekeeping or apiary management',
                'tree-seedling-distribution' => 'Distribution of trees or seedlings for agroforestry, woodlots, or enrichment planting',
            ];

            foreach ($items as $slug => $label) {
                $option = FormOptionListOption::create([
                    'form_option_list_id' => $formOptionList->id,
                    'label' => $label,
                    'slug' => $slug,
                ]);

                if (empty($option->label_id)) {
                    $option->label_id = $this->generateIfMissingI18nItem($option, 'label');
                    $option->save();
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $key = 'income-generating-activities';
        $list = FormOptionList::where('key', $key)->first();
        if ($list) {
            $list->options()->delete();
            $list->delete();
        }
    }

    private function generateIfMissingI18nItem(Model $target, string $property): ?int
    {
        $value = trim((string) data_get($target, $property, ''));
        $short = strlen($value) <= 256;
        if ($value && empty(data_get($target, $property . '_id'))) {
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
