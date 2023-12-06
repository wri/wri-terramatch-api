<?php

namespace App\Imports;

use App\Models\V2\BaselineMonitoring\SiteMetric;
use Illuminate\Database\Eloquent\Model as EloquentModel;
use Maatwebsite\Excel\Concerns\ToModel;

class BaselineMonitoringSiteImport implements ToModel
{
    private $model;

    private $headerList = [
        'tree_count' => ['tree_count'],
        'tree_cover' => ['tree_cover'],
    ];

    public function __construct(EloquentModel $model )
    {
        $this->model = $model;
    }

    public function model(array $row): ?EloquentModel
    {
        $headers = $this->standardiseHeaders($row);

        $data = [
            'monitorable_type' => get_class($this->model),
            'monitorable_id' => $this->model->id,

            'tree_count' => data_get($headers, 'tree_count', null),
            'tree_cover' =>  data_get($headers, 'tree_cover', null),
        ];

        return new SiteMetric($data);
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
