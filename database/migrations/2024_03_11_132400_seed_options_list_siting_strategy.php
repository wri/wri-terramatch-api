<?php

use App\Helpers\I18nHelper;
use App\Models\V2\Forms\FormOptionList;
use App\Models\V2\Forms\FormOptionListOption;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Str;

class SeedOptionsListSitingStrategy extends Migration
{
    /**
     * This migration seeds the default options
     *
     * @return void
     */
    public function up()
    {
        $collections = [
            'siting-strategy-collection' => ['Concentred','Distributed', 'Hybrid'],
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
            $option->label_id = I18nHelper::generateI18nItem($option, 'label');
            $option->save();
        }
    }
}
