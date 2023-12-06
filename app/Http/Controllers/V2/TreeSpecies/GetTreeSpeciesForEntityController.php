<?php

namespace App\Http\Controllers\V2\TreeSpecies;

use App\Http\Controllers\Controller;
use App\Http\Resources\V2\TreeSpecies\TreeSpeciesCollection;
use App\Models\V2\Nurseries\Nursery;
use App\Models\V2\Nurseries\NurseryReport;
use App\Models\V2\Projects\Project;
use App\Models\V2\Projects\ProjectReport;
use App\Models\V2\Sites\Site;
use App\Models\V2\Sites\SiteReport;
use App\Models\V2\TreeSpecies\TreeSpecies;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GetTreeSpeciesForEntityController extends Controller
{
    public function __invoke(Request $request, string $entity, string $uuid)
    {
        $model = $this->getModel($entity);

        if (is_null($model)) {
            return new JsonResponse($entity . ' is not a valid entity key', 422);
        }

        $object = $model::isUuid($uuid)->first();

        $this->authorize('read', $object);

        if (is_null($object)) {
            return new JsonResponse($entity . ' record not found', 404);
        }

        $query = TreeSpecies::query()
            ->where('speciesable_type', $model)
            ->where('speciesable_id', $object->id);

        return new TreeSpeciesCollection($query->paginate());
    }

    private function getModel(string $entity)
    {
        $model = null;

        switch ($entity) {
            case 'project':
                $model = Project::class;

                break;
            case 'site':
                $model = Site::class;

                break;
            case 'nursery':
                $model = Nursery::class;

                break;
            case 'project-report':
                $model = ProjectReport::class;

                break;
            case 'site-report':
                $model = SiteReport::class;

                break;
            case 'nursery-report':
                $model = NurseryReport::class;

                break;
        }

        return $model;
    }
}
