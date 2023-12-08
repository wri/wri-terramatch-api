<?php

namespace App\Http\Controllers\V2\Invasives;

use App\Http\Controllers\Controller;
use App\Http\Resources\V2\Invasives\InvasiveCollection;
use App\Models\V2\Invasive;
use App\Models\V2\Nurseries\Nursery;
use App\Models\V2\Nurseries\NurseryReport;
use App\Models\V2\Projects\Project;
use App\Models\V2\Projects\ProjectReport;
use App\Models\V2\Sites\Site;
use App\Models\V2\Sites\SiteReport;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GetInvasivesForEntityController extends Controller
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

        $query = Invasive::query()
            ->where('invasiveable_type', $model)
            ->where('invasiveable_id', $object->id);

        return new InvasiveCollection($query->paginate());
    }

    private function getModel(string $entity)
    {
        $model = null;

        switch ($entity) {
            case 'project':
            case 'projects':
                $model = Project::class;

                break;
            case 'site':
            case 'sites':
                $model = Site::class;

                break;
            case 'nursery':
            case 'nurseries':
                $model = Nursery::class;

                break;
            case 'project-report':
            case 'project-reports':
                $model = ProjectReport::class;

                break;
            case 'site-report':
            case 'site-reports':
                $model = SiteReport::class;

                break;
            case 'nursery-report':
            case 'nursery-reports':
                $model = NurseryReport::class;

                break;
        }

        return $model;
    }
}
