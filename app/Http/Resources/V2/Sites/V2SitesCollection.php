<?php

namespace App\Http\Resources\V2\Sites;

use App\Models\V2\Sites\Site;
use Illuminate\Http\Resources\Json\ResourceCollection;

class V2SitesCollection extends ResourceCollection
{
    protected $params;

    public function params(array $params = null)
    {
        $this->params = $params;

        return $this;
    }

    public function toArray($request)
    {
        return ['data' => SiteLiteResource::collection($this->collection)];
    }

    public function paginationInformation($request, $paginated, $default)
    {
        $default['meta']['unfiltered_total'] = data_get($this->params, 'unfiltered_total', Site::count());

        return $default;
    }
}
