<?php

use App\Models\V2\Forms\FormOptionList;
use App\Models\V2\Forms\FormOptionListOption;
use Illuminate\Database\Migrations\Migration;

class SwapSlugOnCountriesListOptionToBeCountryCode extends Migration
{
    /**
     * This migration swaps the slug to be the alt value.
     * The data will always be here so this is a safe migration.
     * See 2023_07_26_120922_create_form_option_lists_table.php
     *
     * @return void
     */
    public function up()
    {
        $formOptionList = FormOptionList::where('key', 'countries')->first();
        $options = FormOptionListOption::where('form_option_list_id', $formOptionList->id)->get();

        $options->each(function (FormOptionListOption $formOptionListOption) {
            $formOptionListOption->update([
                'slug' => $formOptionListOption->alt_value,
            ]);
        });
    }
}
