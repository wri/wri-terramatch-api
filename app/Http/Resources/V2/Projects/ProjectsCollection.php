<?php

namespace App\Http\Resources\V2\Projects;

use App\Models\V2\Projects\Project;
use Illuminate\Http\Resources\Json\ResourceCollection;

class ProjectsCollection extends ResourceCollection
{
    public function toArray($request)
    {
        return ['data' => ProjectLiteResource::collection($this->collection)];
    }

    public function paginationInformation($request, $paginated, $default)
    {
        $default['meta']['unfiltered_total'] = Project::count();

        return $default;
    }
}
