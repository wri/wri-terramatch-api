<?php

use App\Models\V2\Forms\FormOptionList;
use App\Models\V2\Forms\FormOptionListOption;
use App\Models\V2\I18n\I18nItem;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Str;

class OptionsForLandTenureProjectArea extends Migration
{
    /**
     * This migration seeds the default options
     *
     * @return void
     */
    public function up()
    {
        $collections = [
            'project-land-tenures' => ['Public', 'Private', 'Indigenous', 'Communal', 'National Protected Area'],
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

    public function down()
    {
        $collections = [
            'project-land-tenures',
        ];

        foreach ($collections as $key) {
            $formOptionList = FormOptionList::where('key', $key)->first();

            if ($formOptionList) {
                FormOptionListOption::where('form_option_list_id', $formOptionList->id)->delete();
                $formOptionList->delete();
            }
        }

        // Note: Depending on your data model and relationships, you might need to adjust the delete logic.

        // If you want to revert the changes made to the I18nItem table, you can do something like this:
        I18nItem::where('type', 'short')->where('status', I18nItem::STATUS_DRAFT)->delete();
        I18nItem::where('type', 'long')->where('status', I18nItem::STATUS_DRAFT)->delete();
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
