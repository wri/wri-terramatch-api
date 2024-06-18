<?php

namespace App\Http\Controllers\V2\TreeSpecies;

use App\Http\Controllers\Controller;
use App\Http\Resources\V2\TreeSpecies\TreeSpeciesCollection;
use App\Models\V2\EntityModel;
use App\Models\V2\TreeSpecies\TreeSpecies;
use Illuminate\Http\Request;

class GetTreeSpeciesForEntityController extends Controller
{
    public function __invoke(Request $request, EntityModel $entity)
    {
        $this->authorize('read', $entity);

        $query = TreeSpecies::query()
            ->where('speciesable_type', get_class($entity))
            ->where('speciesable_id', $entity->id);

        $filter = $request->query('filter');
        if (! empty($filter['collection'])) {
            $query->where('collection', $filter['collection']);
        }

        return new TreeSpeciesCollection($query->paginate());
    }
}
