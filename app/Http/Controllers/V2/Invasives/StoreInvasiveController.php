<?php

namespace App\Http\Controllers\V2\Invasives;

use App\Exceptions\Terrafund\InvalidMorphableModelException;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreInvasivesRequest;
use App\Http\Resources\V2\Invasives\InvasiveResource;
use App\Models\V2\Invasive;
use App\Models\V2\Nurseries\Nursery;
use App\Models\V2\Nurseries\NurseryReport;
use App\Models\V2\Organisation;
use App\Models\V2\ProjectPitch;
use App\Models\V2\Projects\Project;
use App\Models\V2\Projects\ProjectReport;
use App\Models\V2\Sites\Site;
use App\Models\V2\Sites\SiteReport;

class StoreInvasiveController extends Controller
{
    public function __invoke(StoreInvasivesRequest $storeInvasiveRequest): InvasiveResource
    {
        $model = $this->getEntityFromRequest($storeInvasiveRequest);
        $this->authorize('update', $model);

        $storeInvasiveRequest->merge([
            'invasiveable_type' => get_class($model),
            'invasiveable_id' => $model->id,
        ]);

        $invasives = Invasive::create($storeInvasiveRequest->all());

        return new InvasiveResource($invasives);
    }

    private function getEntityFromRequest(StoreInvasivesRequest $request)
    {
        switch ($request->get('model_type')) {
            case 'organisation':
                return Organisation::isUuid($request->get('model_uuid'))->firstOrFail();
            case 'project-pitch':
                return ProjectPitch::isUuid($request->get('model_uuid'))->firstOrFail();
            case 'project':
                return Project::isUuid($request->get('model_uuid'))->firstOrFail();
            case 'project-report':
                return ProjectReport::isUuid($request->get('model_uuid'))->firstOrFail();
            case 'site':
                return Site::isUuid($request->get('model_uuid'))->firstOrFail();
            case 'site-report':
                return SiteReport::isUuid($request->get('model_uuid'))->firstOrFail();
            case 'nursery':
                return Nursery::isUuid($request->get('model_uuid'))->firstOrFail();
            case 'nursery-report':
                return NurseryReport::isUuid($request->get('model_uuid'))->firstOrFail();
            default:
                throw new InvalidMorphableModelException();
        }

        return $request;
    }
}
