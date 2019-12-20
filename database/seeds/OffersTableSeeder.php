<?php

use Illuminate\Database\Seeder;
use App\Models\Offer as OfferModel;

class OffersTableSeeder extends Seeder
{
    public function run()
    {
        $offer = new OfferModel();
        $offer->id = 1;
        $offer->organisation_id = 1;
        $offer->name = "Example Offer";
        $offer->description = "Lorem ipsum dolor sit amet";
        $offer->land_types = ["cropland","mangrove"];
        $offer->land_ownerships = ["private"];
        $offer->land_size = "gt_100";
        $offer->land_continent = "europe";
        $offer->land_country = null;
        $offer->restoration_methods = ["agroforestry", "reserve_corridors", "riparian_buffers"];
        $offer->restoration_goals = ["food"];
        $offer->funding_sources = ["equity_investment", "loan_debt", "grant_with_reporting"];
        $offer->funding_amount = 1000000;
        $offer->price_per_tree = 1.5;
        $offer->long_term_engagement = false;
        $offer->reporting_frequency = "gt_quarterly";
        $offer->reporting_level = "low";
        $offer->sustainable_development_goals = ["goal_1", "goal_7", "goal_9", "goal_13"];
        $offer->saveOrFail();

        $offer = new OfferModel();
        $offer->id = 2;
        $offer->organisation_id = 2;
        $offer->name = "Bar Offer";
        $offer->description = "Lorem ipsum dolor sit amet";
        $offer->land_types = ["wetland","mangrove"];
        $offer->land_ownerships = ["private"];
        $offer->land_size = "lt_100";
        $offer->land_continent = "europe";
        $offer->land_country = null;
        $offer->restoration_methods = ["reserve_corridors", "riparian_buffers"];
        $offer->restoration_goals = ["food"];
        $offer->funding_sources = ["equity_investment", "loan_debt", "grant_with_reporting"];
        $offer->funding_amount = 5000;
        $offer->price_per_tree = 2;
        $offer->long_term_engagement = false;
        $offer->reporting_frequency = "annually";
        $offer->reporting_level = "high";
        $offer->sustainable_development_goals = ["goal_2", "goal_7", "goal_17"];
        $offer->saveOrFail();

        $offer = new OfferModel();
        $offer->id = 3;
        $offer->organisation_id = 2;
        $offer->name = "Baz Offer";
        $offer->description = "Lorem ipsum dolor sit amet";
        $offer->land_types = ["wetland","cropland"];
        $offer->land_ownerships = ["private"];
        $offer->land_size = "gt_100";
        $offer->land_continent = "africa";
        $offer->land_country = null;
        $offer->restoration_methods = [ "riparian_buffers"];
        $offer->restoration_goals = ["food"];
        $offer->funding_sources = ["equity_investment", "grant_with_reporting"];
        $offer->funding_amount = 10000;
        $offer->price_per_tree = 3;
        $offer->long_term_engagement = false;
        $offer->reporting_frequency = "lt_annually";
        $offer->reporting_level = "high";
        $offer->sustainable_development_goals = ["goal_1", "goal_6", "goal_17"];
        $offer->saveOrFail();
    }
}
