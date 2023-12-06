<?php

namespace Database\Seeders;

use App\Models\Offer as OfferModel;
use DateTime;
use DateTimeZone;
use Illuminate\Database\Seeder;

class OffersTableSeeder extends Seeder
{
    public function run()
    {
        $offer = new OfferModel();
        $offer->id = 1;
        $offer->organisation_id = 1;
        $offer->name = 'Example Offer';
        $offer->description = 'Lorem ipsum dolor sit amet';
        $offer->land_types = ['cropland','mangrove'];
        $offer->land_ownerships = ['private'];
        $offer->land_size = 'gt_100';
        $offer->land_continent = 'europe';
        $offer->land_country = null;
        $offer->restoration_methods = ['agroforestry',  'riparian_buffers'];
        $offer->restoration_goals = ['climate'];
        $offer->funding_sources = ['equity_investment', 'loan_debt', 'grant_with_reporting'];
        $offer->funding_amount = 1000000;
        $offer->funding_bracket = 'gt_1m';
        $offer->price_per_tree = 1.5;
        $offer->long_term_engagement = false;
        $offer->reporting_frequency = 'gt_quarterly';
        $offer->reporting_level = 'low';
        $offer->sustainable_development_goals = ['goal_1', 'goal_7', 'goal_9', 'goal_13'];
        $offer->visibility_updated_at = new DateTime('now', new DateTimeZone('UTC'));
        $offer->saveOrFail();

        $offer = new OfferModel();
        $offer->id = 2;
        $offer->organisation_id = 2;
        $offer->name = 'Bar Offer';
        $offer->description = 'Lorem ipsum dolor sit amet';
        $offer->land_types = ['wetland','mangrove'];
        $offer->land_ownerships = ['private'];
        $offer->land_size = 'lt_100';
        $offer->land_continent = 'europe';
        $offer->land_country = null;
        $offer->restoration_methods = ['riparian_buffers'];
        $offer->restoration_goals = ['climate'];
        $offer->funding_sources = ['equity_investment', 'loan_debt', 'grant_with_reporting'];
        $offer->funding_amount = 5000;
        $offer->funding_bracket = 'lt_50k';
        $offer->price_per_tree = 2;
        $offer->long_term_engagement = false;
        $offer->reporting_frequency = 'annually';
        $offer->reporting_level = 'high';
        $offer->sustainable_development_goals = ['goal_2', 'goal_7', 'goal_17'];
        $offer->visibility_updated_at = new DateTime('now', new DateTimeZone('UTC'));
        $offer->saveOrFail();

        $offer = new OfferModel();
        $offer->id = 3;
        $offer->organisation_id = 2;
        $offer->name = 'Baz Offer';
        $offer->description = 'Lorem ipsum dolor sit amet';
        $offer->land_types = ['wetland','cropland'];
        $offer->land_ownerships = ['private'];
        $offer->land_size = 'gt_100';
        $offer->land_continent = 'africa';
        $offer->land_country = null;
        $offer->restoration_methods = [ 'riparian_buffers'];
        $offer->restoration_goals = ['climate'];
        $offer->funding_sources = ['equity_investment', 'grant_with_reporting'];
        $offer->funding_amount = 10000;
        $offer->funding_bracket = 'lt_50k';
        $offer->price_per_tree = 3;
        $offer->long_term_engagement = false;
        $offer->reporting_frequency = 'lt_annually';
        $offer->reporting_level = 'high';
        $offer->sustainable_development_goals = ['goal_1', 'goal_6', 'goal_17'];
        $offer->visibility_updated_at = new DateTime('now', new DateTimeZone('UTC'));
        $offer->saveOrFail();

        $offer = new OfferModel();
        $offer->id = 4;
        $offer->organisation_id = 2;
        $offer->name = 'Qux Offer';
        $offer->description = 'Lorem ipsum dolor sit amet';
        $offer->land_types = ['wetland','cropland'];
        $offer->land_ownerships = ['private'];
        $offer->land_size = 'gt_100';
        $offer->land_continent = 'africa';
        $offer->land_country = null;
        $offer->restoration_methods = [ 'riparian_buffers'];
        $offer->restoration_goals = ['climate'];
        $offer->funding_sources = ['equity_investment', 'grant_with_reporting'];
        $offer->funding_amount = 10000;
        $offer->funding_bracket = 'lt_50k';
        $offer->price_per_tree = 3;
        $offer->long_term_engagement = false;
        $offer->reporting_frequency = 'lt_annually';
        $offer->reporting_level = 'high';
        $offer->sustainable_development_goals = ['goal_1', 'goal_6', 'goal_17'];
        $offer->visibility = 'fully_invested_funded';
        $offer->visibility_updated_at = new DateTime('now', new DateTimeZone('UTC'));
        $offer->saveOrFail();

        $offer = new OfferModel();
        $offer->id = 5;
        $offer->organisation_id = 2;
        $offer->name = 'Norf Offer';
        $offer->description = 'Lorem ipsum dolor sit amet';
        $offer->land_types = ['wetland','cropland'];
        $offer->land_ownerships = ['private'];
        $offer->land_size = 'gt_100';
        $offer->land_continent = 'africa';
        $offer->land_country = null;
        $offer->restoration_methods = [ 'riparian_buffers'];
        $offer->restoration_goals = ['climate'];
        $offer->funding_sources = ['equity_investment', 'grant_with_reporting'];
        $offer->funding_amount = 10000;
        $offer->funding_bracket = 'lt_50k';
        $offer->price_per_tree = 3;
        $offer->long_term_engagement = false;
        $offer->reporting_frequency = 'lt_annually';
        $offer->reporting_level = 'high';
        $offer->sustainable_development_goals = ['goal_1', 'goal_6', 'goal_17'];
        $offer->visibility = 'fully_invested_funded';
        $offer->visibility_updated_at = new DateTime('now', new DateTimeZone('UTC'));
        $offer->saveOrFail();
    }
}
