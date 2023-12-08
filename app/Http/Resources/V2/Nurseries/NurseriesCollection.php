<?php

namespace App\Http\Resources\V2\Nurseries;

use App\Models\V2\Nurseries\Nursery;
use Illuminate\Http\Resources\Json\ResourceCollection;

class NurseriesCollection extends ResourceCollection
{
    protected $params;

    public function params(array $params = null)
    {
        $this->params = $params;

        return $this;
    }

    public function toArray($request)
    {
        return ['data' => NurseryLiteResource::collection($this->collection)];
    }

    public function paginationInformation($request, $paginated, $default)
    {
        $default['meta']['unfiltered_total'] = data_get($this->params, 'unfiltered_total', Nursery::count());

        return $default;
    }
}
