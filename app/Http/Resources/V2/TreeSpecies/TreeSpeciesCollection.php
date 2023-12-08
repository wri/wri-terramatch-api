<?php

namespace App\Http\Resources\V2\TreeSpecies;

use App\Models\V2\TreeSpecies\TreeSpecies;
use Illuminate\Http\Resources\Json\ResourceCollection;

class TreeSpeciesCollection extends ResourceCollection
{
    public function toArray($request)
    {
        return ['data' => TreeSpeciesResource::collection($this->collection)];
    }

    public function paginationInformation($request, $paginated, $default)
    {
        $default['meta']['unfiltered_total'] = TreeSpecies::count();

        return $default;
    }
}
