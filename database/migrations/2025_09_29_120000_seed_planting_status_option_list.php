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
        $key = 'planting-status';

        if (! FormOptionList::where('key', $key)->exists()) {
            $formOptionList = FormOptionList::create(['key' => $key]);

            $items = [
                'no-restoration-expected',
                'not-started',
                'in-progress',
                'completed',
            ];

            foreach ($items as $item) {
                $option = FormOptionListOption::create([
                    'form_option_list_id' => $formOptionList->id,
                    'label' => $item,
                    'slug' => Str::slug($item),
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
        $key = 'planting-status';
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


