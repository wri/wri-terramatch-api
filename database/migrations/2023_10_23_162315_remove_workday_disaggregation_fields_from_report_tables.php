<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RemoveWorkdayDisaggregationFieldsFromReportTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('v2_project_reports', function (Blueprint $table) {
            $table->dropColumn([
                'ethnic_indigenous_1',
                'ethnic_indigenous_2',
                'ethnic_indigenous_3',
                'ethnic_indigenous_4',
                'ethnic_indigenous_5',
                'ethnic_other_1',
                'ethnic_other_2',
                'ethnic_other_3',
                'ethnic_other_4',
                'ethnic_other_5',
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

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
    }
}
