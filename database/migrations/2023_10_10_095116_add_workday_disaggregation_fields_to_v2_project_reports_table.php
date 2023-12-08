<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddWorkdayDisaggregationFieldsToV2ProjectReportsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('v2_project_reports', function (Blueprint $table) {
            // Add the pe_ columns
            $table->unsignedBigInteger('pe_gender_female')->nullable();
            $table->unsignedBigInteger('pe_gender_male')->nullable();
            $table->unsignedBigInteger('pe_gender_undefined')->nullable();
            $table->unsignedBigInteger('pe_age_youth')->nullable();
            $table->unsignedBigInteger('pe_age_adult')->nullable();
            $table->unsignedBigInteger('pe_age_elder')->nullable();
            $table->unsignedBigInteger('pe_age_undefined')->nullable();
            $table->unsignedBigInteger('pe_ethnicity_indigenous_1')->nullable();
            $table->unsignedBigInteger('pe_ethnicity_indigenous_2')->nullable();
            $table->unsignedBigInteger('pe_ethnicity_other_1')->nullable();
            $table->unsignedBigInteger('pe_ethnicity_other_2')->nullable();
            $table->unsignedBigInteger('pe_ethnicity_other_3')->nullable();
            $table->unsignedBigInteger('pe_ethnicity_undefined')->nullable();

            // Add the no_ columns
            $table->unsignedBigInteger('no_gender_female')->nullable();
            $table->unsignedBigInteger('no_gender_male')->nullable();
            $table->unsignedBigInteger('no_gender_undefined')->nullable();
            $table->unsignedBigInteger('no_age_youth')->nullable();
            $table->unsignedBigInteger('no_age_adult')->nullable();
            $table->unsignedBigInteger('no_age_elder')->nullable();
            $table->unsignedBigInteger('no_age_undefined')->nullable();
            $table->unsignedBigInteger('no_ethnicity_indigenous_1')->nullable();
            $table->unsignedBigInteger('no_ethnicity_indigenous_2')->nullable();
            $table->unsignedBigInteger('no_ethnicity_other_1')->nullable();
            $table->unsignedBigInteger('no_ethnicity_other_2')->nullable();
            $table->unsignedBigInteger('no_ethnicity_other_3')->nullable();
            $table->unsignedBigInteger('no_ethnicity_undefined')->nullable();

            // Add the pm_ columns
            $table->unsignedBigInteger('pm_gender_female')->nullable();
            $table->unsignedBigInteger('pm_gender_male')->nullable();
            $table->unsignedBigInteger('pm_gender_undefined')->nullable();
            $table->unsignedBigInteger('pm_age_youth')->nullable();
            $table->unsignedBigInteger('pm_age_adult')->nullable();
            $table->unsignedBigInteger('pm_age_elder')->nullable();
            $table->unsignedBigInteger('pm_age_undefined')->nullable();
            $table->unsignedBigInteger('pm_ethnicity_indigenous_1')->nullable();
            $table->unsignedBigInteger('pm_ethnicity_indigenous_2')->nullable();
            $table->unsignedBigInteger('pm_ethnicity_other_1')->nullable();
            $table->unsignedBigInteger('pm_ethnicity_other_2')->nullable();
            $table->unsignedBigInteger('pm_ethnicity_other_3')->nullable();
            $table->unsignedBigInteger('pm_ethnicity_undefined')->nullable();

            // Add the sc_ columns
            $table->unsignedBigInteger('sc_gender_female')->nullable();
            $table->unsignedBigInteger('sc_gender_male')->nullable();
            $table->unsignedBigInteger('sc_gender_undefined')->nullable();
            $table->unsignedBigInteger('sc_age_youth')->nullable();
            $table->unsignedBigInteger('sc_age_adult')->nullable();
            $table->unsignedBigInteger('sc_age_elder')->nullable();
            $table->unsignedBigInteger('sc_age_undefined')->nullable();
            $table->unsignedBigInteger('sc_ethnicity_indigenous_1')->nullable();
            $table->unsignedBigInteger('sc_ethnicity_indigenous_2')->nullable();
            $table->unsignedBigInteger('sc_ethnicity_other_1')->nullable();
            $table->unsignedBigInteger('sc_ethnicity_other_2')->nullable();
            $table->unsignedBigInteger('sc_ethnicity_other_3')->nullable();
            $table->unsignedBigInteger('sc_ethnicity_undefined')->nullable();

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
        Schema::table('v2_project_reports', function (Blueprint $table) {
            $table->dropColumn([
                'pe_gender_female',
                'pe_gender_male',
                'pe_gender_undefined',
                'pe_age_youth',
                'pe_age_adult',
                'pe_age_elder',
                'pe_age_undefined',
                'pe_ethnicity_indigenous_1',
                'pe_ethnicity_indigenous_2',
                'pe_ethnicity_other_1',
                'pe_ethnicity_other_2',
                'pe_ethnicity_other_3',
                'pe_ethnicity_undefined',
                'no_gender_female',
                'no_gender_male',
                'no_gender_undefined',
                'no_age_youth',
                'no_age_adult',
                'no_age_elder',
                'no_age_undefined',
                'no_ethnicity_indigenous_1',
                'no_ethnicity_indigenous_2',
                'no_ethnicity_other_1',
                'no_ethnicity_other_2',
                'no_ethnicity_other_3',
                'no_ethnicity_undefined',
                'pm_gender_female',
                'pm_gender_male',
                'pm_gender_undefined',
                'pm_age_youth',
                'pm_age_adult',
                'pm_age_elder',
                'pm_age_undefined',
                'pm_ethnicity_indigenous_1',
                'pm_ethnicity_indigenous_2',
                'pm_ethnicity_other_1',
                'pm_ethnicity_other_2',
                'pm_ethnicity_other_3',
                'pm_ethnicity_undefined',
                'sc_gender_female',
                'sc_gender_male',
                'sc_gender_undefined',
                'sc_age_youth',
                'sc_age_adult',
                'sc_age_elder',
                'sc_age_undefined',
                'sc_ethnicity_indigenous_1',
                'sc_ethnicity_indigenous_2',
                'sc_ethnicity_other_1',
                'sc_ethnicity_other_2',
                'sc_ethnicity_other_3',
                'sc_ethnicity_undefined',
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
