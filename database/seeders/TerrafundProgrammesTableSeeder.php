<?php

namespace Database\Seeders;

use App\Models\Terrafund\TerrafundProgramme;
use Illuminate\Database\Seeder;

class TerrafundProgrammesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        $programme = new TerrafundProgramme();
        $programme->id = 1;
        $programme->name = 'Programme name';
        $programme->description = 'Programme description';
        $programme->planting_start_date = '2000-10-06';
        $programme->planting_end_date = '2998-04-24';
        $programme->budget = 12345;
        $programme->status = 'new_project';
        $programme->home_country = 'se';
        $programme->project_country = 'au';
        $programme->boundary_geojson = '{"type":"Polygon","coordinates":[[[-1.864006519317627,50.7219083651253],[-1.8627190589904783,50.7219083651253],[-1.8627190589904783,50.72276418262861],[-1.864006519317627,50.72276418262861],[-1.864006519317627,50.7219083651253]]]}';
        $programme->history = 'history';
        $programme->objectives = 'objectives';
        $programme->environmental_goals = 'environmental goals';
        $programme->socioeconomic_goals = 'socioeconomic goals';
        $programme->sdgs_impacted = 'SDGs impacted';
        $programme->long_term_growth = 'long term growth';
        $programme->community_incentives = 'community incentives';
        $programme->organisation_id = 1;
        $programme->total_hectares_restored = 20000;
        $programme->trees_planted = 12;
        $programme->jobs_created = 100;
        $programme->framework_id = 2;
        $programme->saveOrFail();

        $programme = new TerrafundProgramme();
        $programme->id = 2;
        $programme->name = 'Programme name';
        $programme->description = 'Programme description';
        $programme->planting_start_date = '2000-10-06';
        $programme->planting_end_date = '2998-04-24';
        $programme->budget = 12345;
        $programme->status = 'new_project';
        $programme->home_country = 'se';
        $programme->project_country = 'au';
        $programme->boundary_geojson = '{"type":"Polygon","coordinates":[[[-1.864006519317627,50.7219083651253],[-1.8627190589904783,50.7219083651253],[-1.8627190589904783,50.72276418262861],[-1.864006519317627,50.72276418262861],[-1.864006519317627,50.7219083651253]]]}';
        $programme->history = 'history';
        $programme->objectives = 'objectives';
        $programme->environmental_goals = 'environmental goals';
        $programme->socioeconomic_goals = 'socioeconomic goals';
        $programme->sdgs_impacted = 'SDGs impacted';
        $programme->long_term_growth = 'long term growth';
        $programme->community_incentives = 'community incentives';
        $programme->total_hectares_restored = 20000;
        $programme->trees_planted = 12;
        $programme->jobs_created = 100;
        $programme->framework_id = 2;
        $programme->saveOrFail();
    }
}
