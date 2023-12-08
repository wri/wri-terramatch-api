<?php

use App\Models\V2\Forms\FormOptionList;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateOptionListImages extends Migration
{
    /**
     * This migration seeds the default options
     *
     * @return void
     */
    public function up()
    {
        Schema::table('form_option_list_options', function (Blueprint $table) {
            $table->string('image_url')->after('label_id')->nullable();
        });

        $collections = [
            'sdgs-impacted-type',
            'restoration-systems',
            'land-tenures',
        ];

        foreach ($collections as $key) {
            $formOptionList = FormOptionList::where(['key' => $key])->first();

            foreach ($formOptionList->options as $option) {
                $option->update(['image_url' => '/images/V2/' . $key . '/' . $option->slug . '.png']);
            }
        }
    }
}
