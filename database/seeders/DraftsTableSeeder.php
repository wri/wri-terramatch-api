<?php

namespace Database\Seeders;

use App\Models\Draft as DraftModel;
use App\Models\Drafting\DraftOffer;
use App\Models\Drafting\DraftOrganisation;
use App\Models\Drafting\DraftPitch;
use App\Models\Drafting\DraftProgramme;
use App\Models\Drafting\DraftProgrammeSubmission;
use App\Models\Drafting\DraftSite;
use App\Models\Drafting\DraftSiteSubmission;
use App\Models\Drafting\DraftTerrafundNursery;
use App\Models\Drafting\DraftTerrafundNurserySubmission;
use App\Models\Drafting\DraftTerrafundProgramme;
use App\Models\Drafting\DraftTerrafundProgrammeSubmission;
use App\Models\Drafting\DraftTerrafundSite;
use App\Models\Drafting\DraftTerrafundSiteSubmission;
use Illuminate\Database\Seeder;

class DraftsTableSeeder extends Seeder
{
    public function run()
    {
        $draft = new DraftModel();
        $draft->id = 1;
        $draft->organisation_id = 1;
        $draft->name = 'Foo Offer Draft';
        $draft->type = 'offer';
        $draft->data = json_encode(DraftOffer::BLUEPRINT);
        $draft->created_by = 3;
        $draft->saveOrFail();

        $draft = new DraftModel();
        $draft->id = 2;
        $draft->organisation_id = 1;
        $draft->name = 'Bar Pitch Draft';
        $draft->type = 'pitch';
        $draft->data = json_encode(DraftPitch::BLUEPRINT);
        $draft->created_by = 3;
        $draft->saveOrFail();

        $draft = new DraftModel();
        $draft->id = 3;
        $draft->organisation_id = 1;
        $draft->name = 'Baz Offer Draft';
        $draft->type = 'offer';
        $data = DraftOffer::BLUEPRINT;
        $data['offer']['name'] = 'Bar Offer';
        $data['offer']['description'] = 'Lorem ipsum dolor sit amet';
        $data['offer']['land_types'] = ['cropland','mangrove'];
        $data['offer']['land_ownerships'] = ['private'];
        $data['offer']['land_size'] = 'gt_100';
        $data['offer']['land_continent'] = 'europe';
        $data['offer']['land_country'] = null;
        $data['offer']['restoration_methods'] = ['agroforestry', 'riparian_buffers'];
        $data['offer']['restoration_goals'] = ['climate'];
        $data['offer']['funding_sources'] = ['equity_investment', 'loan_debt', 'grant_with_reporting'];
        $data['offer']['funding_amount'] = 123456;
        $data['offer']['funding_bracket'] = 'gt_1m';
        $data['offer']['price_per_tree'] = 1;
        $data['offer']['long_term_engagement'] = true;
        $data['offer']['reporting_frequency'] = 'gt_quarterly';
        $data['offer']['reporting_level'] = 'high';
        $data['offer']['sustainable_development_goals'] = ['goal_2', 'goal_4', 'goal_13'];
        $data['offer_contacts'][0] = [];
        $data['offer_contacts'][0]['user_id'] = 6;
        $draft->data = json_encode($data);
        $draft->created_by = 3;
        $draft->saveOrFail();

        $draft = new DraftModel();
        $draft->id = 4;
        $draft->organisation_id = 1;
        $draft->name = 'Qux Pitch Draft';
        $draft->type = 'pitch';
        $data = DraftPitch::BLUEPRINT;
        $data['pitch']['name'] = 'Example Pitch';
        $data['pitch']['description'] = 'Lorem ipsum dolor sit amet';
        $data['pitch']['land_types'] = ['bare_land', 'wetland'];
        $data['pitch']['land_ownerships'] = ['public'];
        $data['pitch']['land_size'] = '10_to_100';
        $data['pitch']['land_continent'] = 'australia';
        $data['pitch']['land_country'] = 'AU';
        $data['pitch']['land_geojson'] = '{"type":"Polygon","coordinates":[[[-1.864006519317627,50.7219083651253],[-1.8627190589904783,50.7219083651253],[-1.8627190589904783,50.72276418262861],[-1.864006519317627,50.72276418262861],[-1.864006519317627,50.7219083651253]]]}';
        $data['pitch']['restoration_methods'] = ['assisted_natural', 'riparian_buffers'];
        $data['pitch']['restoration_goals'] = ['agriculture_and_commodities'];
        $data['pitch']['funding_sources'] = ['grant_with_limited_reporting'];
        $data['pitch']['funding_amount'] = 1234;
        $data['pitch']['funding_bracket'] = 'lt_50k';
        $data['pitch']['revenue_drivers'] = ['carbon_credits'];
        $data['pitch']['estimated_timespan'] = 36;
        $data['pitch']['long_term_engagement'] = null;
        $data['pitch']['reporting_frequency'] = 'bi_annually';
        $data['pitch']['reporting_level'] = 'high';
        $data['pitch']['sustainable_development_goals'] = ['goal_5', 'goal_7'];
        $data['pitch']['cover_photo'] = null;
        $data['pitch']['video'] = 1;
        $data['pitch']['problem'] = 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur.';
        $data['pitch']['anticipated_outcome'] = 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur.';
        $data['pitch']['who_is_involved'] = 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.';
        $data['pitch']['local_community_involvement'] = true;
        $data['pitch']['training_involved'] = true;
        $data['pitch']['training_type'] = 'remote';
        $data['pitch']['training_amount_people'] = 33;
        $data['pitch']['people_working_in'] = 'test string';
        $data['pitch']['people_amount_nearby'] = 10;
        $data['pitch']['people_amount_abroad'] = 3;
        $data['pitch']['people_amount_employees'] = 4;
        $data['pitch']['people_amount_volunteers'] = 5;
        $data['pitch']['benefited_people'] = 404;
        $data['pitch']['future_maintenance'] = 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.';
        $data['pitch']['use_of_resources'] = 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.';
        $data['pitch_contacts'][0] = [];
        $data['pitch_contacts'][0]['team_member_id'] = 1;
        $draft->data = json_encode($data);
        $draft->created_by = 3;
        $draft->saveOrFail();

        $draft = new DraftModel();
        $draft->id = 5;
        $draft->organisation_id = 1;
        $draft->name = 'Norf Programme Draft';
        $draft->type = 'programme';
        $data = DraftProgramme::BLUEPRINT;
        $data['programme']['name'] = 'name';
        $data['programme']['country'] = 'SE';
        $data['programme']['continent'] = 'europe';
        $data['programme']['thumbnail'] = 11;
        $data['programme']['end_date'] = '2031-08-07';
        $data['boundary']['boundary_geojson'] = '{"type":"Polygon","coordinates":[[[-1.864006519317627,50.7219083651253],[-1.8627190589904783,50.7219083651253],[-1.8627190589904783,50.72276418262861],[-1.864006519317627,50.72276418262861],[-1.864006519317627,50.7219083651253]]]}';
        $data['programme_tree_species_file'] = null;
        $data['programme_tree_species'][0] = 'a tree';
        $data['aims']['year_five_trees'] = 123;
        $data['aims']['restoration_hectares'] = 1000;
        $data['aims']['survival_rate'] = 50;
        $data['aims']['year_five_crown_cover'] = 34;
        $draft->data = json_encode($data);
        $draft->created_by = 3;
        $draft->saveOrFail();

        $draft = new DraftModel();
        $draft->id = 6;
        $draft->organisation_id = 1;
        $draft->name = 'Quuz Site Draft';
        $draft->type = 'site';
        $data = DraftSite::BLUEPRINT;
        $data['site']['programme_id'] = 1;
        $data['site']['site_name'] = 'site name';
        $data['site']['site_description'] = 'site description';
        $data['site']['site_history'] = 'site history';
        $data['site']['end_date'] = '2031-08-07';
        $data['site']['planting_pattern'] = 'some planting pattern';
        $data['site']['stratification_for_heterogeneity'] = 15;
        $data['boundary']['boundary_geojson'] = '{"type":"Polygon","coordinates":[[[-1.864006519317627,50.7219083651253],[-1.8627190589904783,50.7219083651253],[-1.8627190589904783,50.72276418262861],[-1.864006519317627,50.72276418262861],[-1.864006519317627,50.7219083651253]]]}';
        $data['aims']['aim_survival_rate'] = 50;
        $data['aims']['aim_year_five_crown_cover'] = 34;
        $data['aims']['aim_direct_seeding_survival_rate'] = 99;
        $data['aims']['aim_natural_regeneration_trees_per_hectare'] = 10;
        $data['aims']['aim_natural_regeneration_hectares'] = 6;
        $data['aims']['aim_soil_condition'] = 'good';
        $data['aims']['aim_number_of_mature_trees'] = 10;
        $data['narratives']['public_narrative'] = 'public narrative';
        $data['narratives']['technical_narrative'] = 'technical narrative';
        $data['establishment_date']['establishment_date'] = '2021-10-06';
        $data['restoration_methods']['site_restoration_method_ids'] = [1];
        $data['socioeconomic_benefits'] = 8;
        $data['site_tree_species_file'] = null;
        $data['site_tree_species'][0] = 'a tree';
        $data['land_tenure'][0] = 1;
        $data['seeds'][0]['name'] = 'test';
        $data['seeds'][0]['amount'] = 25;
        $data['seeds'][0]['weight_of_sample'] = 1.3;
        $data['seeds'][0]['seeds_in_sample'] = 2716;
        $data['invasives'][0]['name'] = 'invasive plant';
        $data['invasives'][0]['type'] = 'common';
        $data['media'][0]['is_public'] = false;
        $data['media'][0]['upload'] = 3;
        $data['media'][0]['media_title'] = 'some name';
        $data['media'][0]['location_long'] = 10;
        $data['media'][0]['location_lat'] = 15;
        $data['progress']['invasives_skipped'] = true;
        $draft->data = json_encode($data);
        $draft->created_by = 3;
        $draft->saveOrFail();

        $draft = new DraftModel();
        $draft->id = 7;
        $draft->organisation_id = 1;
        $draft->name = 'Corge Site Submission Draft';
        $draft->type = 'site_submission';
        $draft->due_submission_id = 2;
        $data = DraftSiteSubmission::BLUEPRINT;
        $data['socioeconomic_benefits'] = 9;
        $data['site_submission']['site_id'] = 1;
        $data['site_submission']['created_by'] = 'testing user';
        $data['site_tree_species_file'] = null;
        $data['site_tree_species'][0]['name'] = 'a tree';
        $data['site_tree_species'][0]['amount'] = 100;
        $data['narratives']['public_narrative'] = 'public narrative';
        $data['narratives']['technical_narrative'] = 'technical narrative';
        $data['media'][0]['is_public'] = false;
        $data['media'][0]['upload'] = 2;
        $data['media'][0]['media_title'] = 'some name';
        $data['media'][0]['location_long'] = 10;
        $data['media'][0]['location_lat'] = 15;
        $data['direct_seeding']['direct_seeding_kg'] = 100;
        $data['direct_seeding']['kg_by_species'][0]['name'] = 'some seeds';
        $data['direct_seeding']['kg_by_species'][0]['weight'] = 123;
        $data['disturbances'][0]['disturbance_type'] = 'manmade';
        $data['disturbances'][0]['extent'] = '21-40';
        $data['disturbances'][0]['intensity'] = 'medium';
        $data['disturbances'][0]['description'] = 'description';
        $data['disturbance_information'] = 'information about disturbances';
        $data['progress']['jobs_and_livelihoods_skipped'] = true;
        $data['progress']['trees_planted_skipped'] = true;
        $data['progress']['disturbances_skipped'] = false;
        $data['progress']['direct_seeding_skipped'] = true;
        $draft->data = json_encode($data);
        $draft->created_by = 3;
        $draft->saveOrFail();

        $draft = new DraftModel();
        $draft->id = 8;
        $draft->organisation_id = 1;
        $draft->name = 'Mulligan Programme Submission Draft';
        $draft->type = 'programme_submission';
        $draft->due_submission_id = 1;
        $data = DraftProgrammeSubmission::BLUEPRINT;
        $data['programme_submission']['programme_id'] = 1;
        $data['programme_submission']['title'] = 'SPS';
        $data['programme_submission']['created_by'] = 'testing user';
        $data['programme_tree_species_file'] = null;
        $data['programme_tree_species'][0]['name'] = 'a tree';
        $data['programme_tree_species'][0]['amount'] = 100;
        $data['socioeconomic_benefits'] = 17;
        $data['narratives']['public_narrative'] = 'public narrative';
        $data['narratives']['technical_narrative'] = 'technical narrative';
        $data['media'][0]['is_public'] = false;
        $data['media'][0]['upload'] = 10;
        $data['media'][0]['media_title'] = 'programme submission media name';
        $data['media'][0]['location_long'] = 10;
        $data['media'][0]['location_lat'] = 15;
        $data['progress']['trees_planted_skipped'] = false;
        $data['progress']['jobs_and_livelihoods_skipped'] = true;
        $draft->data = json_encode($data);
        $draft->created_by = 3;
        $draft->saveOrFail();

        $draft = new DraftModel();
        $draft->id = 9;
        $draft->organisation_id = 1;
        $draft->due_submission_id = 2;
        $draft->name = 'To Merge Site Submission Draft';
        $draft->type = 'site_submission';
        $data = DraftSiteSubmission::BLUEPRINT;
        $data['socioeconomic_benefits'] = 12;
        $data['site_submission']['site_id'] = 1;
        $data['site_submission']['created_by'] = 'testing user';
        $data['site_tree_species_file'] = null;
        $data['site_tree_species'][0]['name'] = 'another tree';
        $data['site_tree_species'][0]['amount'] = 100;
        $data['narratives']['public_narrative'] = null;
        $data['narratives']['technical_narrative'] = 'technical narrative';
        $data['media'][0]['is_public'] = false;
        $data['media'][0]['upload'] = 13;
        $data['media'][0]['media_title'] = 'some name';
        $data['media'][0]['location_long'] = 10;
        $data['media'][0]['location_lat'] = 15;
        $data['direct_seeding']['direct_seeding_kg'] = 100;
        $data['direct_seeding']['kg_by_species'][0]['name'] = 'some seeds';
        $data['direct_seeding']['kg_by_species'][0]['weight'] = 200;
        $data['disturbances'][0]['disturbance_type'] = 'manmade';
        $data['disturbances'][0]['extent'] = '21-40';
        $data['disturbances'][0]['intensity'] = 'medium';
        $data['disturbances'][0]['description'] = 'description';
        $data['disturbance_information'] = 'information about disturbances';
        $data['progress']['jobs_and_livelihoods_skipped'] = false;
        $data['progress']['trees_planted_skipped'] = true;
        $data['progress']['disturbances_skipped'] = false;
        $data['progress']['direct_seeding_skipped'] = true;
        $draft->data = json_encode($data);
        $draft->created_by = 3;
        $draft->saveOrFail();

        $draft = new DraftModel();
        $draft->id = 10;
        $draft->organisation_id = 1;
        $draft->name = 'To Merge Programme Submission Draft';
        $draft->type = 'programme_submission';
        $data = DraftProgrammeSubmission::BLUEPRINT;
        $data['programme_submission']['programme_id'] = 1;
        $data['programme_submission']['title'] = 'SPS';
        $data['programme_tree_species_file'] = null;
        $data['programme_tree_species'][0]['name'] = 'another tree';
        $data['programme_tree_species'][0]['amount'] = 50;
        $data['narratives']['public_narrative'] = 'public narrative';
        $data['narratives']['technical_narrative'] = 'technical narrative';
        $data['media'][0]['is_public'] = false;
        $data['media'][0]['upload'] = 14;
        $data['media'][0]['media_title'] = 'programme submission media name';
        $data['media'][0]['location_long'] = 10;
        $data['media'][0]['location_lat'] = 15;
        $data['progress']['jobs_and_livelihoods_skipped'] = false;
        $data['progress']['trees_planted_skipped'] = false;
        $draft->data = json_encode($data);
        $draft->created_by = 3;
        $draft->saveOrFail();

        $draft = new DraftModel();
        $draft->id = 11;
        $draft->organisation_id = 1;
        $draft->name = 'Terrafund Programme Draft';
        $draft->type = 'terrafund_programme';
        $data = DraftTerrafundProgramme::BLUEPRINT;
        $data['terrafund_programme']['name'] = 'draft terrafund programme';
        $data['terrafund_programme']['description'] = 'draft terrafund description';
        $data['terrafund_programme']['planting_start_date'] = '2020-01-01';
        $data['terrafund_programme']['planting_end_date'] = '2021-01-01';
        $data['terrafund_programme']['budget'] = 10000;
        $data['terrafund_programme']['status'] = 'new_project';
        $data['terrafund_programme']['project_country'] = 'SE';
        $data['terrafund_programme']['home_country'] = null;
        $data['terrafund_programme']['boundary_geojson'] = '{"type":"Polygon","coordinates":[[[-1.864006519317627,50.7219083651253],[-1.8627190589904783,50.7219083651253],[-1.8627190589904783,50.72276418262861],[-1.864006519317627,50.72276418262861],[-1.864006519317627,50.7219083651253]]]}';
        $data['terrafund_programme']['history'] = 'draft terrafund programme history';
        $data['terrafund_programme']['objectives'] = 'draft terrafund programme objectives';
        $data['terrafund_programme']['environmental_goals'] = 'draft terrafund programme environmental goals';
        $data['terrafund_programme']['socioeconomic_goals'] = 'draft terrafund programme socioeconomic goals';
        $data['terrafund_programme']['sdgs_impacted'] = 'draft terrafund sdgs impacted';
        $data['terrafund_programme']['long_term_growth'] = 'draft terrafund long term growth';
        $data['terrafund_programme']['community_incentives'] = 'draft terrafund community incentives';
        $data['terrafund_programme']['total_hectares_restored'] = 50000;
        $data['terrafund_programme']['trees_planted'] = 1234;
        $data['terrafund_programme']['jobs_created'] = 200;
        $data['tree_species'][0]['name'] = 'draft terrafund tree';
        $data['tree_species'][0]['amount'] = 10;
        $data['additional_files'][0]['is_public'] = false;
        $data['additional_files'][0]['upload'] = 18;
        $draft->data = json_encode($data);
        $draft->created_by = 3;
        $draft->saveOrFail();

        $draft = new DraftModel();
        $draft->id = 12;
        $draft->organisation_id = 1;
        $draft->name = 'Terrafund Nursery Draft';
        $draft->type = 'terrafund_nursery';
        $data = DraftTerrafundNursery::BLUEPRINT;
        $data['terrafund_nursery']['name'] = 'draft terrafund nursery';
        $data['terrafund_nursery']['start_date'] = '2021-08-07';
        $data['terrafund_nursery']['end_date'] = '2021-11-06';
        $data['terrafund_nursery']['seedling_grown'] = 1;
        $data['terrafund_nursery']['planting_contribution'] = 'planting contribution';
        $data['terrafund_nursery']['nursery_type'] = 'building';
        $data['tree_species'][0]['name'] = 'draft terrafund nursery tree';
        $data['tree_species'][0]['amount'] = 100;
        $data['photos'][0]['is_public'] = false;
        $data['photos'][0]['upload'] = 19;
        $draft->data = json_encode($data);
        $draft->created_by = 3;
        $draft->saveOrFail();

        $draft = new DraftModel();
        $draft->id = 13;
        $draft->name = 'Organisation Draft';
        $draft->type = 'organisation';
        $data = DraftOrganisation::BLUEPRINT;
        $data['organisation']['name'] = 'Acme Corporation';
        $data['organisation']['description'] = 'Lorem ipsum dolor sit amet';
        $data['organisation']['address_1'] = '1 Foo Road';
        $data['organisation']['address_2'] = null;
        $data['organisation']['city'] = 'Bar Town';
        $data['organisation']['state'] = 'Baz State';
        $data['organisation']['zip_code'] = 'Qux';
        $data['organisation']['country'] = 'GB';
        $data['organisation']['phone_number'] = '0123456789';
        $data['organisation']['full_time_permanent_employees'] = '200';
        $data['organisation']['seasonal_employees'] = '30';
        $data['organisation']['part_time_permanent_employees'] = '100';
        $data['organisation']['percentage_female'] = '50';
        $data['organisation']['percentage_youth'] = '5';
        $data['organisation']['website'] = 'http://www.example.com';
        $data['organisation']['key_contact'] = 'jeffrey';
        $data['organisation']['type'] = 'other';
        $data['organisation']['account_type'] = 'ppc';
        $data['organisation']['category'] = 'both';
        $data['organisation']['facebook'] = null;
        $data['organisation']['twitter'] = null;
        $data['organisation']['linkedin'] = null;
        $data['organisation']['instagram'] = null;
        $data['organisation']['avatar'] = 37;
        $data['organisation']['cover_photo'] = 38;
        $data['organisation']['video'] = null;
        $data['organisation']['founded_at'] = '2000-01-01';
        $data['organisation']['community_engagement_strategy'] = 'strategy';
        $data['organisation']['three_year_community_engagement'] = 'engagement';
        $data['organisation']['women_farmer_engagement'] = 57;
        $data['organisation']['young_people_engagement'] = 89;
        $data['organisation']['monitoring_and_evaluation_experience'] = 'experience';
        $data['organisation']['community_follow_up'] = 'follow up';
        $data['organisation']['total_hectares_restored'] = 1010;
        $data['organisation']['hectares_restored_three_years'] = 4321;
        $data['organisation']['total_trees_grown'] = 10000;
        $data['organisation']['tree_survival_rate'] = 90;
        $data['organisation']['tree_maintenance_and_aftercare'] = 'some maintenance';
        $data['organisation']['key_contact'] = 'a key contact';
        $data['organisation']['revenues_19'] = 1000;
        $data['organisation']['revenues_20'] = 1000;
        $data['organisation']['revenues_21'] = 1000;
        $data['photos'][0]['is_public'] = false;
        $data['photos'][0]['upload'] = 22;
        $data['files'][0]['type'] = 'financial_statement';
        $data['files'][0]['upload'] = 21;
        $draft->data = json_encode($data);
        $draft->created_by = 13;
        $draft->saveOrFail();

        $draft = new DraftModel();
        $draft->id = 14;
        $draft->organisation_id = 1;
        $draft->name = 'Terrafund Site Draft';
        $draft->type = 'terrafund_site';
        $data = DraftTerrafundSite::BLUEPRINT;
        $data['terrafund_site']['name'] = 'draft terrafund nursery';
        $data['terrafund_site']['start_date'] = '2021-08-07';
        $data['terrafund_site']['end_date'] = '2021-11-06';
        $data['terrafund_site']['boundary_geojson'] = '{"type":"Polygon","coordinates":[[[-1.864006519317627,50.7219083651253],[-1.8627190589904783,50.7219083651253],[-1.8627190589904783,50.72276418262861],[-1.864006519317627,50.72276418262861],[-1.864006519317627,50.7219083651253]]]}';
        $data['terrafund_site']['restoration_methods'] = ['plantations', 'agroforestry'];
        $data['terrafund_site']['land_tenures'] = ['public'];
        $data['terrafund_site']['hectares_to_restore'] = 100;
        $data['terrafund_site']['landscape_community_contribution'] = 'the community contribution';
        $data['terrafund_site']['disturbances'] = 'the disturbances on this site';
        $data['photos'][0]['is_public'] = false;
        $data['photos'][0]['upload'] = 24;
        $draft->data = json_encode($data);
        $draft->created_by = 3;
        $draft->saveOrFail();

        $draft = new DraftModel();
        $draft->id = 15;
        $draft->organisation_id = 1;
        $draft->terrafund_due_submission_id = 2;
        $draft->name = 'Terrafund Nursery Submission Draft';
        $draft->type = 'terrafund_nursery_submission';
        $data = DraftTerrafundNurserySubmission::BLUEPRINT;
        $data['terrafund_nursery_submission']['seedlings_young_trees'] = 12345;
        $data['terrafund_nursery_submission']['interesting_facts'] = 'some interesting facts';
        $data['terrafund_nursery_submission']['site_prep'] = 'the site prep';
        $data['terrafund_nursery_submission']['shared_drive_link'] = 'https://www.google.com/';
        $data['terrafund_nursery_submission']['terrafund_nursery_id'] = 1;
        $data['photos'][0]['is_public'] = false;
        $data['photos'][0]['upload'] = 25;
        $draft->data = json_encode($data);
        $draft->created_by = 3;
        $draft->saveOrFail();

        $draft = new DraftModel();
        $draft->id = 16;
        $draft->organisation_id = 1;
        $draft->name = 'Terrafund Site Submission Draft';
        $draft->type = 'terrafund_site_submission';
        $data = DraftTerrafundSiteSubmission::BLUEPRINT;
        $data['terrafund_site_submission']['terrafund_site_id'] = 1;
        $data['terrafund_site_submission']['shared_drive_link'] = 'https://www.google.com/';
        $photosArray = [];
        for ($index = 27; $index <= 36; $index++) {
            array_push($photosArray, [
                'is_public' => false,
                'upload' => $index,
            ]);
        }
        $data['photos'] = $photosArray;
        $data['tree_species'][0]['name'] = 'draft terrafund site submission tree';
        $data['tree_species'][0]['amount'] = 100;
        $data['non_tree_species'][0]['name'] = 'draft terrafund site submission non tree';
        $data['non_tree_species'][0]['amount'] = 2;
        $data['disturbances'][0]['type'] = 'manmade';
        $data['disturbances'][0]['description'] = 'this is the disturbance description';
        $draft->data = json_encode($data);
        $draft->created_by = 3;
        $draft->saveOrFail();

        $draft = new DraftModel();
        $draft->id = 17;
        $draft->organisation_id = 1;
        $draft->name = 'Terrafund Programme Submission Draft';
        $draft->type = 'terrafund_programme_submission';
        $data = DraftTerrafundProgrammeSubmission::BLUEPRINT;
        $data['terrafund_programme_submission']['shared_drive_link'] = 'https://www.google.com/';
        $data['terrafund_programme_submission']['landscape_community_contribution'] = 'option_1';
        $data['terrafund_programme_submission']['top_three_successes'] = 'Successes';
        $data['terrafund_programme_submission']['maintenance_and_monitoring_activities'] = 'Activities';
        $data['terrafund_programme_submission']['significant_change'] = 'Significant changes';
        $data['terrafund_programme_submission']['percentage_survival_to_date'] = 80;
        $data['terrafund_programme_submission']['survival_calculation'] = 'Survival calc';
        $data['terrafund_programme_submission']['survival_comparison'] = 'Survival comp';
        $data['terrafund_programme_submission']['ft_women'] = 10000;
        $data['terrafund_programme_submission']['ft_men'] = 10000;
        $data['terrafund_programme_submission']['ft_youth'] = 5000;
        $data['terrafund_programme_submission']['ft_total'] = 7500;
        $data['terrafund_programme_submission']['pt_women'] = 10000;
        $data['terrafund_programme_submission']['pt_men'] = 10000;
        $data['terrafund_programme_submission']['pt_youth'] = 5000;
        $data['terrafund_programme_submission']['pt_total'] = 7500;
        $data['terrafund_programme_submission']['volunteer_women'] = 10000;
        $data['terrafund_programme_submission']['volunteer_men'] = 10000;
        $data['terrafund_programme_submission']['volunteer_youth'] = 5000;
        $data['terrafund_programme_submission']['volunteer_total'] = 7500;
        $data['terrafund_programme_submission']['people_annual_income_increased'] = 1212;
        $data['terrafund_programme_submission']['people_knowledge_skills_increased'] = 9000;
        $data['photos'][0]['is_public'] = false;
        $data['photos'][0]['upload'] = 39;
        $data['other_additional_documents'][0]['is_public'] = false;
        $data['other_additional_documents'][0]['upload'] = 42;
        $draft->data = json_encode($data);
        $draft->created_by = 3;
        $draft->saveOrFail();
    }
}
