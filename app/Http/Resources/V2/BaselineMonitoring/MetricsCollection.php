<?php

namespace App\Http\Resources\V2\BaselineMonitoring;

use Illuminate\Http\Resources\Json\ResourceCollection;

class MetricsCollection extends ResourceCollection
{
    public function toArray($request)
    {
        return ['data' => $this->collection];
    }
}
