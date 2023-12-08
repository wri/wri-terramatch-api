<?php

namespace App\Http\Resources\V2\Workdays;

use Illuminate\Http\Resources\Json\ResourceCollection;

class WorkdaysCollection extends ResourceCollection
{
    protected $params;

    public function params(array $params = null)
    {
        $this->params = $params;

        return $this;
    }

    public function toArray($request)
    {
        return ['data' => WorkdayResource::collection($this->collection)];
    }

    public function paginationInformation($request, $paginated, $default)
    {
        $default['meta']['count_total'] = data_get($this->params, 'count_total');

        return $default;
    }
}
