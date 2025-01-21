<?php

namespace App\Http\Controllers\V2\Entities;

use App\Http\Controllers\Controller;
use App\Http\Resources\V2\TreeSpecies\TreeSpeciesCollection;
use App\Http\Resources\V2\TreeSpecies\TreeSpeciesTransformer;
use App\Models\V2\Disturbance;
use App\Models\V2\EntityModel;
use App\Models\V2\EntityRelationModel;
use App\Models\V2\Invasive;
use App\Models\V2\Projects\Project;
use App\Models\V2\Projects\ProjectReport;
use App\Models\V2\Seeding;
use App\Models\V2\Sites\Site;
use App\Models\V2\Stratas\Strata;
use App\Models\V2\TreeSpecies\TreeSpecies;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class GetRelationsForEntityController extends Controller
{
    public const RELATIONS = [
        'tree-species' => TreeSpecies::class,
        'disturbances' => Disturbance::class,
        'stratas' => Strata::class,
        'invasives' => Invasive::class,
        'seedings' => Seeding::class,
    ];

    private const TREE_SPECIES_ENTITIES = [
        Project::class,
        Site::class,
        ProjectReport::class,
    ];

    public function __invoke(Request $request, string $relationType, EntityModel $entity): JsonResource
    {
        $this->authorize('read', $entity);

        if ($relationType === 'tree-species' && in_array(get_class($entity), self::TREE_SPECIES_ENTITIES)) {
            return $this->handleTreeSpecies($request, $entity);
        }

        /** @var EntityRelationModel $type */
        $type = self::RELATIONS[$relationType];

        return $type::createResourceCollection($entity);
    }

    private function handleTreeSpecies(Request $request, EntityModel $entity): JsonResource
    {
        $query = TreeSpecies::query()
            ->where('speciesable_type', get_class($entity))
            ->where('speciesable_id', $entity->id)
            ->visible();

        if ($filter = $request->query('filter')) {
            if (! empty($filter['collection'])) {
                $query->where('collection', $filter['collection']);
                $entityTreeSpecies = $query->get();

                $transformer = new TreeSpeciesTransformer($entity, $entityTreeSpecies, $filter['collection']);
                $transformedData = $transformer->transform();

                return new TreeSpeciesCollection($transformedData);
            }

            if (! empty($filter['collection'])) {
                $query->where('collection', $filter['collection']);
            }
        }

        return new TreeSpeciesCollection($query->get());
    }
}
