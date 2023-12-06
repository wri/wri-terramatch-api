<?php

namespace App\Http\Controllers\V2\UpdateRequests;

use App\Http\Controllers\Controller;
use App\Http\Resources\V2\UpdateRequests\UpdateRequestResource;
use App\Models\V2\Nurseries\Nursery;
use App\Models\V2\Nurseries\NurseryReport;
use App\Models\V2\Projects\Project;
use App\Models\V2\Projects\ProjectReport;
use App\Models\V2\Sites\Site;
use App\Models\V2\Sites\SiteReport;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EntityUpdateRequestsController extends Controller
{
    public function __invoke(Request $request, string $entity, string $uuid)
    {
        switch($entity) {
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

        if (empty($model)) {
            return new JsonResponse($entity . ' is not a valid entity key', 422);
        }

        $object = $model::isUuid($uuid)->first();

        $this->authorize('read', $object);

        if (empty($object)) {
            return new JsonResponse($entity . ' record not found', 404);
        }

        $latest = $object->updateRequests()
            ->orderBy('updated_at', 'DESC')
            ->first();

        if (is_null($latest)) {
            return new JsonResponse('There is not any update request for this resource', 404);
        }

        return new UpdateRequestResource($latest);
    }
}
