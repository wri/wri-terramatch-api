<?php

namespace Database\Seeders;

use App\Models\Target as TargetModel;
use Illuminate\Database\Seeder;

class TargetsTableSeeder extends Seeder
{
    public function run()
    {
        $target = new TargetModel();
        $target->id = 1;
        $target->monitoring_id = 1;
        $target->negotiator = 'pitch';
        $target->start_date = '2020-01-01';
        $target->finish_date = '2021-01-01';
        $target->funding_amount = 1000000;
        $target->land_geojson = '{"type":"Polygon","coordinates":[[[-1.864006519317627,50.7219083651253],[-1.8627190589904783,50.7219083651253],[-1.8627190589904783,50.72276418262861],[-1.864006519317627,50.72276418262861],[-1.864006519317627,50.7219083651253]]]}';
        $target->data = [
            'trees_planted' => 123,
            'non_trees_planted' => 456,
            'survival_rate' => 75,
            'land_size_planted' => 7.5,
            'land_size_restored' => 7.5,
        ];
        $target->created_by = 3;
        $target->accepted_by = 4;
        $target->saveOrFail();

        $target = new TargetModel();
        $target->id = 2;
        $target->monitoring_id = 1;
        $target->negotiator = 'offer';
        $target->start_date = '2020-01-01';
        $target->finish_date = '2021-01-01';
        $target->funding_amount = 1000000;
        $target->land_geojson = '{"type":"FeatureCollection","features":[{"type":"Feature","properties":{},"geometry":{"type":"Polygon","coordinates":[[[-1.8619894981384275,50.721799688768364],[-1.8586206436157227,50.721799688768364],[-1.8586206436157227,50.72318529343996],[-1.8619894981384275,50.72318529343996],[-1.8619894981384275,50.721799688768364]]]}}]}';
        $target->data = [
            'trees_planted' => 789,
            'non_trees_planted' => 456,
            'survival_rate' => 75,
            'land_size_planted' => 7.5,
            'land_size_restored' => 7.5,
            'carbon_captures' => 123,
        ];
        $target->created_by = 4;
        $target->created_at = now()->addSecond();
        $target->updated_at = now()->addSecond();
        $target->saveOrFail();

        $target = new TargetModel();
        $target->id = 3;
        $target->monitoring_id = 2;
        $target->negotiator = 'pitch';
        $target->start_date = '2020-01-01';
        $target->finish_date = '2021-01-01';
        $target->funding_amount = 1000000;
        $target->land_geojson = '{"type":"Polygon","coordinates":[[[-1.864006519317627,50.7219083651253],[-1.8627190589904783,50.7219083651253],[-1.8627190589904783,50.72276418262861],[-1.864006519317627,50.72276418262861],[-1.864006519317627,50.7219083651253]]]}';
        $target->data = [
            'trees_planted' => 123,
            'non_trees_planted' => 456,
            'survival_rate' => 75,
            'land_size_planted' => 7.5,
            'land_size_restored' => 7.5,
        ];
        $target->created_by = 3;
        $target->accepted_at = '2021-01-01 00:00:00';
        $target->accepted_by = 4;
        $target->saveOrFail();
    }
}
