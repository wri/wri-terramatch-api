<?php

namespace App\Imports;

use App\Models\V2\BaselineMonitoring\ProjectMetric;
use Illuminate\Database\Eloquent\Model as EloquentModel;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class BaselineMonitoringProjectImport implements ToModel, WithHeadingRow
{
    private $model;

    private $headerList = [
        'total_hectares' => ['total_hectares_under_restoration_count', 'total_hectares_under_restoration', 'total_hectares'],
        'ha_mangrove' => ['ha_mangrove', 'mangrove_restoration'],
        'ha_assisted' => ['ha_assisted', 'assisted_natural_regeneration'],
        'ha_agroforestry' => ['ha_agroforestry', 'agroforestry'],
        'ha_reforestation' => ['ha_reforestation', 'tree_planting_Reforestation'],
        'ha_peatland' => ['ha_peatland', 'peatland_restoration'],
        'ha_riparian' => ['ha_riparian', 'riparian_restoration'],
        'ha_enrichment' => ['ha_enrichment', 'enrichment_planting'],
        'ha_nucleation' => ['ha_nucleation', 'applied_nucleation'],
        'ha_silvopasture' => ['ha_silvopasture', 'Silvopasture'],
        'ha_direct' => ['ha_direct', 'direct_seeding'],
        'tree_count' => ['tree_count'],
        'tree_cover' => ['tree_cover'],
        'tree_cover_loss' => ['tree_cover_loss', 'tree_cover_loss_2001_2021'],
        'carbon_benefits' => ['carbon_benefits'],
        'number_of_esrp' => ['number_of_ecosystem_services_restoration_partners', 'number_of_esrp'],
        'field_tree_count' => ['tree_count_field'],
        'field_tree_regenerated' => ['Number of trees naturally regenerating', 'field_tree_regenerated'],
        'field_tree_survival_percent' => ['percent_survival_of_planted_trees', 'field_tree_survival_percent'],
    ];

    public function __construct(EloquentModel $model )
    {
        $this->model = $model;
    }

    public function model(array $row): ProjectMetric
    {
        $headers = $this->standardiseHeaders($row);

        $data = [
            'monitorable_type' => get_class($this->model),
            'monitorable_id' => $this->model->id,

            'total_hectares' => data_get($headers, 'total_hectares', null),
            'ha_mangrove' => data_get($headers, 'ha_mangrove', null),
            'ha_assisted' => data_get($headers, 'ha_assisted', null),
            'ha_agroforestry' =>data_get($headers, 'ha_agroforestry', null),
            'ha_reforestation' =>data_get($headers, 'ha_reforestation', null),
            'ha_peatland' => data_get($headers, 'ha_peatland', null),
            'ha_riparian' => data_get($headers, 'ha_riparian', null),
            'ha_enrichment' => data_get($headers, 'ha_enrichment', null),
            'ha_nucleation' => data_get($headers, 'ha_nucleation', null),
            'ha_silvopasture' => data_get($headers, 'ha_silvopasture', null),
            'ha_direct' => data_get($headers, 'ha_direct', null),

            'tree_count' => data_get($headers, 'tree_count', null),
            'tree_cover' =>  data_get($headers, 'tree_cover', null),
            'tree_cover_loss' =>  data_get($headers, 'tree_cover_loss', null),
            'carbon_benefits' =>  data_get($headers, 'carbon_benefits', null),
            'number_of_esrp' =>  data_get($headers, 'number_of_esrp', null),
            'field_tree_count' =>  data_get($headers, 'field_tree_count', null),
            'field_tree_regenerated' =>  data_get($headers, 'tree_count', null),
            'field_tree_survival_percent' =>  data_get($headers, 'tree_count', null),
        ];

        return new ProjectMetric($data);
    }

    private function standardiseHeaders(array $row): array
    {
        $headers = [];
        foreach($row as $key=>$value){
            foreach($this->headerList as $field=>$possible){
                if(in_array($key, $possible)){
                    $headers[$field] = $value;
                }
            }
        }
        return $headers;
    }
}
