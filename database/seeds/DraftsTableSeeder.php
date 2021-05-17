<?php

use App\Helpers\DraftHelper;
use App\Models\Draft as DraftModel;
use Illuminate\Database\Seeder;

class DraftsTableSeeder extends Seeder
{
    public function run()
    {
        $draft = new DraftModel();
        $draft->id = 1;
        $draft->organisation_id = 1;
        $draft->name = "Foo Offer Draft";
        $draft->type = "offer";
        $draft->data = json_encode(DraftHelper::EMPTY_DATA_OFFER);
        $draft->created_by = 3;
        $draft->saveOrFail();

        $draft = new DraftModel();
        $draft->id = 2;
        $draft->organisation_id = 1;
        $draft->name = "Bar Pitch Draft";
        $draft->type = "pitch";
        $draft->data = json_encode(DraftHelper::EMPTY_DATA_PITCH);
        $draft->created_by = 3;
        $draft->saveOrFail();

        $draft = new DraftModel();
        $draft->id = 3;
        $draft->organisation_id = 1;
        $draft->name = "Baz Offer Draft";
        $draft->type = "offer";
        $data = DraftHelper::EMPTY_DATA_OFFER;
        $data["offer"]["name"] = "Bar Offer";
        $data["offer"]["description"] = "Lorem ipsum dolor sit amet";
        $data["offer"]["land_types"] = ["cropland","mangrove"];
        $data["offer"]["land_ownerships"] = ["private"];
        $data["offer"]["land_size"] = "gt_100";
        $data["offer"]["land_continent"] = "europe";
        $data["offer"]["land_country"] = null;
        $data["offer"]["restoration_methods"] = ["agroforestry", "riparian_buffers"];
        $data["offer"]["restoration_goals"] = ["climate"];
        $data["offer"]["funding_sources"] = ["equity_investment", "loan_debt", "grant_with_reporting"];
        $data["offer"]["funding_amount"] = 123456;
        $data["offer"]["funding_bracket"] = "gt_1m";
        $data["offer"]["price_per_tree"] = 1;
        $data["offer"]["long_term_engagement"] = true;
        $data["offer"]["reporting_frequency"] = "gt_quarterly";
        $data["offer"]["reporting_level"] = "high";
        $data["offer"]["sustainable_development_goals"] = ["goal_2", "goal_4", "goal_13"];
        $data["offer_contacts"][0] = [];
        $data["offer_contacts"][0]["user_id"] = 6;
        $draft->data = json_encode($data);
        $draft->created_by = 3;
        $draft->saveOrFail();

        $draft = new DraftModel();
        $draft->id = 4;
        $draft->organisation_id = 1;
        $draft->name = "Qux Pitch Draft";
        $draft->type = "pitch";
        $data = DraftHelper::EMPTY_DATA_PITCH;
        $data["pitch"]["name"] = "Example Pitch";
        $data["pitch"]["description"] = "Lorem ipsum dolor sit amet";
        $data["pitch"]["land_types"] = ["bare_land", "wetland"];
        $data["pitch"]["land_ownerships"] = ["public"];
        $data["pitch"]["land_size"] = "10_to_100";
        $data["pitch"]["land_continent"] = "australia";
        $data["pitch"]["land_country"] = "AU";
        $data["pitch"]["land_geojson"] = "{\"type\":\"Polygon\",\"coordinates\":[[[-1.864006519317627,50.7219083651253],[-1.8627190589904783,50.7219083651253],[-1.8627190589904783,50.72276418262861],[-1.864006519317627,50.72276418262861],[-1.864006519317627,50.7219083651253]]]}";
        $data["pitch"]["restoration_methods"] = ["assisted_natural", "riparian_buffers"];
        $data["pitch"]["restoration_goals"] = ["agriculture_and_commodities"];
        $data["pitch"]["funding_sources"] = ["grant_with_limited_reporting"];
        $data["pitch"]["funding_amount"] = 1234;
        $data["pitch"]["funding_bracket"] = "lt_50k";
        $data["pitch"]["revenue_drivers"] = ["carbon_credits"];
        $data["pitch"]["estimated_timespan"] = 36;
        $data["pitch"]["long_term_engagement"] = null;
        $data["pitch"]["reporting_frequency"] = "bi_annually";
        $data["pitch"]["reporting_level"] = "high";
        $data["pitch"]["sustainable_development_goals"] = ["goal_5", "goal_7"];
        $data["pitch"]["cover_photo"] = null;
        $data["pitch"]["video"] = 1;
        $data["pitch"]["problem"] = 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur.';
        $data["pitch"]["anticipated_outcome"] = 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur.';
        $data["pitch"]["who_is_involved"] = "Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.";
        $data["pitch"]["local_community_involvement"] = true;
        $data["pitch"]["training_involved"] = true;
        $data["pitch"]["training_type"] = "remote";
        $data["pitch"]["training_amount_people"] = 33;
        $data["pitch"]["people_working_in"] = 'test string';
        $data["pitch"]["people_amount_nearby"] = 10;
        $data["pitch"]["people_amount_abroad"] = 3;
        $data["pitch"]["people_amount_employees"] = 4;
        $data["pitch"]["people_amount_volunteers"] = 5;
        $data["pitch"]["benefited_people"] = 404;
        $data["pitch"]["future_maintenance"] = "Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.";
        $data["pitch"]["use_of_resources"] = "Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.";
        $data["pitch_contacts"][0] = [];
        $data["pitch_contacts"][0]["team_member_id"] = 1;
        $draft->data = json_encode($data);
        $draft->created_by = 3;
        $draft->saveOrFail();
    }
}
