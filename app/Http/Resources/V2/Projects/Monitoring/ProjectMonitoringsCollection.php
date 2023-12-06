<?php

namespace App\Http\Resources\V2\Projects\Monitoring;

use App\Models\V2\Projects\ProjectMonitoring;
use Illuminate\Http\Resources\Json\ResourceCollection;

class ProjectMonitoringsCollection extends ResourceCollection
{
    public function toArray($request)
    {
        return ['data' => ProjectMonitoringResource::collection($this->collection)];
    }

    public function paginationInformation($request, $paginated, $default)
    {
        $default['meta']['unfiltered_total'] = ProjectMonitoring::count();

        return $default;
    }
}
