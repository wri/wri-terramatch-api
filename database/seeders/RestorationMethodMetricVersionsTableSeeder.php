<?php

namespace Database\Seeders;

use App\Models\RestorationMethodMetricVersion as RestorationMethodMetricVersionModel;
use Illuminate\Database\Seeder;

class RestorationMethodMetricVersionsTableSeeder extends Seeder
{
    public function run()
    {
        $restorationMethodMetricVersion = new RestorationMethodMetricVersionModel();
        $restorationMethodMetricVersion->id = 1;
        $restorationMethodMetricVersion->status = 'approved';
        $restorationMethodMetricVersion->approved_rejected_by = 1;
        $restorationMethodMetricVersion->restoration_method_metric_id = 1;
        $restorationMethodMetricVersion->restoration_method = 'agroforestry';
        $restorationMethodMetricVersion->experience = 6;
        $restorationMethodMetricVersion->land_size = 1;
        $restorationMethodMetricVersion->price_per_hectare = 1.5;
        $restorationMethodMetricVersion->biomass_per_hectare = 10;
        $restorationMethodMetricVersion->carbon_impact = 5;
        $restorationMethodMetricVersion->species_impacted = ['Frog', 'Badger', 'Weasel'];
        $restorationMethodMetricVersion->saveOrFail();

        $restorationMethodMetricVersion = new RestorationMethodMetricVersionModel();
        $restorationMethodMetricVersion->id = 2;
        $restorationMethodMetricVersion->status = 'pending';
        $restorationMethodMetricVersion->restoration_method_metric_id = 1;
        $restorationMethodMetricVersion->restoration_method = 'agroforestry';
        $restorationMethodMetricVersion->experience = 4;
        $restorationMethodMetricVersion->land_size = 1;
        $restorationMethodMetricVersion->price_per_hectare = 2;
        $restorationMethodMetricVersion->biomass_per_hectare = 10;
        $restorationMethodMetricVersion->carbon_impact = 5;
        $restorationMethodMetricVersion->species_impacted = ['Cat', 'Bat', 'Beaver'];
        $restorationMethodMetricVersion->saveOrFail();

        $restorationMethodMetricVersion = new RestorationMethodMetricVersionModel();
        $restorationMethodMetricVersion->id = 3;
        $restorationMethodMetricVersion->status = 'rejected';
        $restorationMethodMetricVersion->restoration_method_metric_id = 1;
        $restorationMethodMetricVersion->restoration_method = 'agroforestry';
        $restorationMethodMetricVersion->experience = 4;
        $restorationMethodMetricVersion->land_size = 1;
        $restorationMethodMetricVersion->price_per_hectare = 2;
        $restorationMethodMetricVersion->biomass_per_hectare = 10;
        $restorationMethodMetricVersion->carbon_impact = 5;
        $restorationMethodMetricVersion->species_impacted = ['Cat', 'Bat', 'Beaver'];
        $restorationMethodMetricVersion->saveOrFail();
    }
}
