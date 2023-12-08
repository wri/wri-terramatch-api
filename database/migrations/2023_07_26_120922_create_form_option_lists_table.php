<?php

use App\Models\V2\Forms\FormOptionList;
use App\Models\V2\Forms\FormOptionListOption;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class CreateFormOptionListsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('form_option_lists', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid');
            $table->string('key')->unique();
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('form_option_list_options', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid');
            $table->foreignIdFor(FormOptionList::class);
            $table->string('slug')->nullable()->index();
            $table->string('alt_value')->nullable()->index();
            $table->string('label')->nullable();
            $table->integer('label_id')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });

        $countryList = FormOptionList::create([
            'key' => 'countries',
        ]);

        foreach (config('wri.countries') as $key => $country) {
            FormOptionListOption::create([
                'slug' => Str::slug($country),
                'label' => $country,
                'alt_value' => $key,
                'form_option_list_id' => $countryList->id,
            ]);
        }

        $monthList = FormOptionList::create([
            'key' => 'months',
        ]);

        foreach (config('wri.months') as $key => $month) {
            FormOptionListOption::create([
                'slug' => Str::slug($month),
                'label' => $month,
                'alt_value' => $key,
                'form_option_list_id' => $monthList->id,
            ]);
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('form_option_lists');
        Schema::dropIfExists('form_option_lists_options');
    }
}
