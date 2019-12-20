<?php

use Illuminate\Database\Seeder;
use App\Models\PitchVersion as PitchVersionModel;

class PitchVersionsTableSeeder extends Seeder
{
    public function run()
    {
        $pitchVersion = new PitchVersionModel();
        $pitchVersion->id = 1;
        $pitchVersion->pitch_id = 1;
        $pitchVersion->status = "approved";
        $pitchVersion->name = "Example Pitch";
        $pitchVersion->description = "Lorem ipsum dolor sit amet";
        $pitchVersion->land_types = ["bare_land", "wetland"];
        $pitchVersion->land_ownerships = ["reserve"];
        $pitchVersion->land_size = "10_to_100";
        $pitchVersion->land_continent = "australia";
        $pitchVersion->land_country = "AU";
        $pitchVersion->land_geojson = "{\"type\":\"Polygon\",\"coordinates\":[[[-1.864006519317627,50.7219083651253],[-1.8627190589904783,50.7219083651253],[-1.8627190589904783,50.72276418262861],[-1.864006519317627,50.72276418262861],[-1.864006519317627,50.7219083651253]]]}";
        $pitchVersion->restoration_methods = ["assisted_natural", "ecological", "riparian_buffers"];
        $pitchVersion->restoration_goals = ["agriculture_and_commodities"];
        $pitchVersion->funding_sources = ["grant_with_limited_reporting"];
        $pitchVersion->funding_amount = 1234;
        $pitchVersion->revenue_drivers = ["carbon_credits"];
        $pitchVersion->estimated_timespan = 36;
        $pitchVersion->long_term_engagement = null;
        $pitchVersion->reporting_frequency = "bi_annually";
        $pitchVersion->reporting_level = "high";
        $pitchVersion->sustainable_development_goals = ["goal_5", "goal_7"];
        $pitchVersion->avatar = null;
        $pitchVersion->cover_photo = null;
        $pitchVersion->video = null;
        $pitchVersion->problem = 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum';
        $pitchVersion->anticipated_outcome = 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum';
        $pitchVersion->who_is_involved = "Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum";
        $pitchVersion->local_community_involvement = true;
        $pitchVersion->training_involved = true;
        $pitchVersion->training_type  = "remote";
        $pitchVersion->training_amount_people = 33;
        $pitchVersion->people_working_in = 'test string';
        $pitchVersion->people_amount_nearby = 10;
        $pitchVersion->people_amount_abroad = 3;
        $pitchVersion->people_amount_employees = 4;
        $pitchVersion->people_amount_volunteers = 5;
        $pitchVersion->benefited_people = 404;
        $pitchVersion->future_maintenance = "Lorem ipsum dolor sit amet, consec...";
        $pitchVersion->use_of_resources = "Lorem ipsum dolor sit amet, consec...";
        $pitchVersion->facebook = null;
        $pitchVersion->twitter = 'https://twitter.com/Twitter';
        $pitchVersion->instagram = 'https://www.instagram.com/asdfasdfasdfadsf/';
        $pitchVersion->saveOrFail();

        $pitchVersion = new PitchVersionModel();
        $pitchVersion->id = 2;
        $pitchVersion->pitch_id = 1;
        $pitchVersion->status = "pending";
        $pitchVersion->name = "Example Pitch";
        $pitchVersion->description = "Lorem ipsum dolor sit amet";
        $pitchVersion->land_types = ["bare_land", "wetland"];
        $pitchVersion->land_ownerships = ["reserve"];
        $pitchVersion->land_size = "10_to_100";
        $pitchVersion->land_continent = "australia";
        $pitchVersion->land_country = "AU";
        $pitchVersion->land_geojson = "{\"type\":\"Polygon\",\"coordinates\":[[[-1.864006519317627,50.7219083651253],[-1.8627190589904783,50.7219083651253],[-1.8627190589904783,50.72276418262861],[-1.864006519317627,50.72276418262861],[-1.864006519317627,50.7219083651253]]]}";
        $pitchVersion->restoration_methods = ["assisted_natural", "ecological", "riparian_buffers"];
        $pitchVersion->restoration_goals = ["agriculture_and_commodities"];
        $pitchVersion->funding_sources = ["grant_with_limited_reporting"];
        $pitchVersion->funding_amount = 1234;
        $pitchVersion->revenue_drivers = ["carbon_credits"];
        $pitchVersion->estimated_timespan = 36;
        $pitchVersion->long_term_engagement = null;
        $pitchVersion->reporting_frequency = "annually";
        $pitchVersion->reporting_level = "high";
        $pitchVersion->sustainable_development_goals = ["goal_5", "goal_7"];
        $pitchVersion->avatar = null;
        $pitchVersion->cover_photo = null;
        $pitchVersion->video = null;
        $pitchVersion->problem = 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum';
        $pitchVersion->anticipated_outcome = 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum';
        $pitchVersion->who_is_involved = "Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum";
        $pitchVersion->local_community_involvement = true;
        $pitchVersion->training_involved = true;
        $pitchVersion->training_type  = "remote";
        $pitchVersion->training_amount_people = 33;
        $pitchVersion->people_working_in = 'test string';
        $pitchVersion->people_amount_nearby = 10;
        $pitchVersion->people_amount_abroad = 3;
        $pitchVersion->people_amount_employees = 4;
        $pitchVersion->people_amount_volunteers = 5;
        $pitchVersion->benefited_people = 404;
        $pitchVersion->future_maintenance = "Lorem ipsum dolor sit amet, consec...";
        $pitchVersion->use_of_resources = "Lorem ipsum dolor sit amet, consec...";
        $pitchVersion->facebook = null;
        $pitchVersion->twitter = 'https://twitter.com/Twitter';
        $pitchVersion->instagram = 'https://www.instagram.com/asdfasdfasdfadsf/';
        $pitchVersion->saveOrFail();
    }
}
