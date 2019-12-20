<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class IndexColumnsForSpeed extends Migration
{
    public function up()
    {
        // offers
        DB::statement("CREATE INDEX offers_land_types_index ON offers ( land_types(256) );");
        DB::statement("CREATE INDEX offers_land_ownerships_index ON offers ( land_ownerships(256) );");
        DB::statement("CREATE INDEX offers_land_size_index ON offers ( land_size );");
        DB::statement("CREATE INDEX offers_land_continent_index ON offers ( land_continent );");
        DB::statement("CREATE INDEX offers_land_country_index ON offers ( land_country );");
        DB::statement("CREATE INDEX offers_restoration_methods_index ON offers ( restoration_methods(256) );");
        DB::statement("CREATE INDEX offers_restoration_goals_index ON offers ( restoration_goals(256) );");
        DB::statement("CREATE INDEX offers_funding_sources_index ON offers ( funding_sources(256) );");
        DB::statement("CREATE INDEX offers_funding_amount_index ON offers ( funding_amount );");
        DB::statement("CREATE INDEX offers_long_term_engagement_index ON offers ( long_term_engagement );");
        DB::statement("CREATE INDEX offers_reporting_frequency_index ON offers ( reporting_frequency );");
        DB::statement("CREATE INDEX offers_reporting_level_index ON offers ( reporting_level );");
        DB::statement("CREATE INDEX offers_sustainable_development_goals_index ON offers ( sustainable_development_goals(256) );");
        // pitch versions
        DB::statement("CREATE INDEX pitch_versions_land_types_index ON pitch_versions ( land_types(256) );");
        DB::statement("CREATE INDEX pitch_versions_land_ownerships_index ON pitch_versions ( land_ownerships(256) );");
        DB::statement("CREATE INDEX pitch_versions_land_size_index ON pitch_versions ( land_size );");
        DB::statement("CREATE INDEX pitch_versions_land_continent_index ON pitch_versions ( land_continent );");
        DB::statement("CREATE INDEX pitch_versions_land_country_index ON pitch_versions ( land_country );");
        DB::statement("CREATE INDEX pitch_versions_restoration_methods_index ON pitch_versions ( restoration_methods(256) );");
        DB::statement("CREATE INDEX pitch_versions_restoration_goals_index ON pitch_versions ( restoration_goals(256) );");
        DB::statement("CREATE INDEX pitch_versions_funding_sources_index ON pitch_versions ( funding_sources(256) );");
        DB::statement("CREATE INDEX pitch_versions_funding_amount_index ON pitch_versions ( funding_amount );");
        DB::statement("CREATE INDEX pitch_versions_long_term_engagement_index ON pitch_versions ( long_term_engagement );");
        DB::statement("CREATE INDEX pitch_versions_reporting_frequency_index ON pitch_versions ( reporting_frequency );");
        DB::statement("CREATE INDEX pitch_versions_reporting_level_index ON pitch_versions ( reporting_level );");
        DB::statement("CREATE INDEX pitch_versions_sustainable_development_goals_index ON pitch_versions ( sustainable_development_goals(256) );");
    }

    public function down()
    {
    }
}
