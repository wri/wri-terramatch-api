<?php

namespace App\Http\Controllers\V2\Disturbances;

use App\Exceptions\Terrafund\InvalidMorphableModelException;
use App\Http\Controllers\Controller;
use App\Http\Requests\V2\Disturbances\StoreDisturbanceRequest;
use App\Http\Resources\V2\Disturbances\DisturbanceResource;
use App\Models\V2\Disturbance;
use App\Models\V2\Nurseries\Nursery;
use App\Models\V2\Nurseries\NurseryReport;
use App\Models\V2\Organisation;
use App\Models\V2\ProjectPitch;
use App\Models\V2\Projects\Project;
use App\Models\V2\Projects\ProjectReport;
use App\Models\V2\Sites\Site;
use App\Models\V2\Sites\SiteReport;

class StoreDisturbanceController extends Controller
{
    public function __invoke(StoreDisturbanceRequest $storeDisturbanceRequest): DisturbanceResource
    {
        $model = $this->getEntityFromRequest($storeDisturbanceRequest);
        $this->authorize('update', $model);

        $storeDisturbanceRequest->merge([
            'disturbanceable_type' => get_class($model),
            'disturbanceable_id' => $model->id,
        ]);

        $strata = Disturbance::create($storeDisturbanceRequest->all());

        return new DisturbanceResource($strata);
    }

    private function getEntityFromRequest(StoreDisturbanceRequest $request)
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
