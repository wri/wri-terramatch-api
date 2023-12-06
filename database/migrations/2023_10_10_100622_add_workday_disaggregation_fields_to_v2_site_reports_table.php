<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddWorkdayDisaggregationFieldsToV2SiteReportsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('v2_site_reports', function (Blueprint $table) {
            // Add the se_ columns
            $table->unsignedBigInteger('se_gender_female')->nullable();
            $table->unsignedBigInteger('se_gender_male')->nullable();
            $table->unsignedBigInteger('se_gender_undefined')->nullable();
            $table->unsignedBigInteger('se_age_youth')->nullable();
            $table->unsignedBigInteger('se_age_adult')->nullable();
            $table->unsignedBigInteger('se_age_elder')->nullable();
            $table->unsignedBigInteger('se_age_undefined')->nullable();
            $table->unsignedBigInteger('se_ethnicity_indigenous_1')->nullable();
            $table->unsignedBigInteger('se_ethnicity_indigenous_2')->nullable();
            $table->unsignedBigInteger('se_ethnicity_other_1')->nullable();
            $table->unsignedBigInteger('se_ethnicity_other_2')->nullable();
            $table->unsignedBigInteger('se_ethnicity_other_3')->nullable();
            $table->unsignedBigInteger('se_ethnicity_undefined')->nullable();

            // Add the p_ columns
            $table->unsignedBigInteger('p_gender_female')->nullable();
            $table->unsignedBigInteger('p_gender_male')->nullable();
            $table->unsignedBigInteger('p_gender_undefined')->nullable();
            $table->unsignedBigInteger('p_age_youth')->nullable();
            $table->unsignedBigInteger('p_age_adult')->nullable();
            $table->unsignedBigInteger('p_age_elder')->nullable();
            $table->unsignedBigInteger('p_age_undefined')->nullable();
            $table->unsignedBigInteger('p_ethnicity_indigenous_1')->nullable();
            $table->unsignedBigInteger('p_ethnicity_indigenous_2')->nullable();
            $table->unsignedBigInteger('p_ethnicity_other_1')->nullable();
            $table->unsignedBigInteger('p_ethnicity_other_2')->nullable();
            $table->unsignedBigInteger('p_ethnicity_other_3')->nullable();
            $table->unsignedBigInteger('p_ethnicity_undefined')->nullable();

            // Add the sma_ columns
            $table->unsignedBigInteger('sma_gender_female')->nullable();
            $table->unsignedBigInteger('sma_gender_male')->nullable();
            $table->unsignedBigInteger('sma_gender_undefined')->nullable();
            $table->unsignedBigInteger('sma_age_youth')->nullable();
            $table->unsignedBigInteger('sma_age_adult')->nullable();
            $table->unsignedBigInteger('sma_age_elder')->nullable();
            $table->unsignedBigInteger('sma_age_undefined')->nullable();
            $table->unsignedBigInteger('sma_ethnicity_indigenous_1')->nullable();
            $table->unsignedBigInteger('sma_ethnicity_indigenous_2')->nullable();
            $table->unsignedBigInteger('sma_ethnicity_other_1')->nullable();
            $table->unsignedBigInteger('sma_ethnicity_other_2')->nullable();
            $table->unsignedBigInteger('sma_ethnicity_other_3')->nullable();
            $table->unsignedBigInteger('sma_ethnicity_undefined')->nullable();

            // Add the smo_ columns
            $table->unsignedBigInteger('smo_gender_female')->nullable();
            $table->unsignedBigInteger('smo_gender_male')->nullable();
            $table->unsignedBigInteger('smo_gender_undefined')->nullable();
            $table->unsignedBigInteger('smo_age_youth')->nullable();
            $table->unsignedBigInteger('smo_age_adult')->nullable();
            $table->unsignedBigInteger('smo_age_elder')->nullable();
            $table->unsignedBigInteger('smo_age_undefined')->nullable();
            $table->unsignedBigInteger('smo_ethnicity_indigenous_1')->nullable();
            $table->unsignedBigInteger('smo_ethnicity_indigenous_2')->nullable();
            $table->unsignedBigInteger('smo_ethnicity_other_1')->nullable();
            $table->unsignedBigInteger('smo_ethnicity_other_2')->nullable();
            $table->unsignedBigInteger('smo_ethnicity_other_3')->nullable();
            $table->unsignedBigInteger('smo_ethnicity_undefined')->nullable();

            // Add the o_ columns
            $table->unsignedBigInteger('o_gender_female')->nullable();
            $table->unsignedBigInteger('o_gender_male')->nullable();
            $table->unsignedBigInteger('o_gender_undefined')->nullable();
            $table->unsignedBigInteger('o_age_youth')->nullable();
            $table->unsignedBigInteger('o_age_adult')->nullable();
            $table->unsignedBigInteger('o_age_elder')->nullable();
            $table->unsignedBigInteger('o_age_undefined')->nullable();
            $table->unsignedBigInteger('o_ethnicity_indigenous_1')->nullable();
            $table->unsignedBigInteger('o_ethnicity_indigenous_2')->nullable();
            $table->unsignedBigInteger('o_ethnicity_other_1')->nullable();
            $table->unsignedBigInteger('o_ethnicity_other_2')->nullable();
            $table->unsignedBigInteger('o_ethnicity_other_3')->nullable();
            $table->unsignedBigInteger('o_ethnicity_undefined')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('v2_site_reports', function (Blueprint $table) {
            $table->dropColumn([
                'se_gender_female',
                'se_gender_male',
                'se_gender_undefined',
                'se_age_youth',
                'se_age_adult',
                'se_age_elder',
                'se_age_undefined',
                'se_ethnicity_indigenous_1',
                'se_ethnicity_indigenous_2',
                'se_ethnicity_other_1',
                'se_ethnicity_other_2',
                'se_ethnicity_other_3',
                'se_ethnicity_undefined',
                'p_gender_female',
                'p_gender_male',
                'p_gender_undefined',
                'p_age_youth',
                'p_age_adult',
                'p_age_elder',
                'p_age_undefined',
                'p_ethnicity_indigenous_1',
                'p_ethnicity_indigenous_2',
                'p_ethnicity_other_1',
                'p_ethnicity_other_2',
                'p_ethnicity_other_3',
                'p_ethnicity_undefined',
                'sma_gender_female',
                'sma_gender_male',
                'sma_gender_undefined',
                'sma_age_youth',
                'sma_age_adult',
                'sma_age_elder',
                'sma_age_undefined',
                'sma_ethnicity_indigenous_1',
                'sma_ethnicity_indigenous_2',
                'sma_ethnicity_other_1',
                'sma_ethnicity_other_2',
                'sma_ethnicity_other_3',
                'sma_ethnicity_undefined',
                'smo_gender_female',
                'smo_gender_male',
                'smo_gender_undefined',
                'smo_age_youth',
                'smo_age_adult',
                'smo_age_elder',
                'smo_age_undefined',
                'smo_ethnicity_indigenous_1',
                'smo_ethnicity_indigenous_2',
                'smo_ethnicity_other_1',
                'smo_ethnicity_other_2',
                'smo_ethnicity_other_3',
                'smo_ethnicity_undefined',
                'o_gender_female',
                'o_gender_male',
                'o_gender_undefined',
                'o_age_youth',
                'o_age_adult',
                'o_age_elder',
                'o_age_undefined',
                'o_ethnicity_indigenous_1',
                'o_ethnicity_indigenous_2',
                'o_ethnicity_other_1',
                'o_ethnicity_other_2',
                'o_ethnicity_other_3',
                'o_ethnicity_undefined',
            ]);
        });
    }
}
