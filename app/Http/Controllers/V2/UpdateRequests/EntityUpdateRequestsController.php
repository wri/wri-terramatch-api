<?php

namespace App\Http\Controllers\V2\UpdateRequests;

use App\Http\Controllers\Controller;
use App\Http\Resources\V2\UpdateRequests\UpdateRequestResource;
use App\Models\V2\EntityModel;
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
    public function __invoke(Request $request, EntityModel $entity)
    {
        $this->authorize('read', $entity);
        $latest = $entity->updateRequests()->isUnapproved()->orderBy('updated_at', 'DESC')->first();

        if (is_null($latest)) {
            return new JsonResponse('There is not any update request for this resource', 404);
        }

        return new UpdateRequestResource($latest);
    }
}
