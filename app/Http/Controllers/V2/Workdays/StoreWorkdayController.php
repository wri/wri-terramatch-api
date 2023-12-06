<?php

namespace App\Http\Controllers\V2\Workdays;

use App\Exceptions\Terrafund\InvalidMorphableModelException;
use App\Http\Controllers\Controller;
use App\Http\Requests\V2\Workdays\StoreWorkdayRequest;
use App\Http\Resources\V2\Workdays\WorkdayResource;
use App\Models\V2\Nurseries\Nursery;
use App\Models\V2\Nurseries\NurseryReport;
use App\Models\V2\Organisation;
use App\Models\V2\ProjectPitch;
use App\Models\V2\Projects\Project;
use App\Models\V2\Projects\ProjectReport;
use App\Models\V2\Sites\Site;
use App\Models\V2\Sites\SiteReport;
use App\Models\V2\Workdays\Workday;

class StoreWorkdayController extends Controller
{
    public function __invoke(StoreWorkdayRequest $request): WorkdayResource
    {
        $model = $this->getEntityFromRequest($request);
        $this->authorize('read', $model);

        $request->merge([
            'workdayable_type' => get_class($model),
            'workdayable_id' => $model->id,
        ]);

        $workday = Workday::create($request->all());

        return new WorkdayResource($workday);
    }

    private function getEntityFromRequest(StoreWorkdayRequest $request)
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
