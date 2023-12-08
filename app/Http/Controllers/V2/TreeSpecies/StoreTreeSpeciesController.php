<?php

namespace App\Http\Controllers\V2\TreeSpecies;

use App\Exceptions\Terrafund\InvalidMorphableModelException;
use App\Http\Controllers\Controller;
use App\Http\Requests\V2\TreeSpecies\StoreTreeSpeciesRequest;
use App\Http\Resources\V2\TreeSpecies\TreeSpeciesResource;
use App\Models\V2\Nurseries\Nursery;
use App\Models\V2\Nurseries\NurseryReport;
use App\Models\V2\Organisation;
use App\Models\V2\ProjectPitch;
use App\Models\V2\Projects\Project;
use App\Models\V2\Projects\ProjectReport;
use App\Models\V2\Sites\Site;
use App\Models\V2\Sites\SiteReport;
use App\Models\V2\TreeSpecies\TreeSpecies;

class StoreTreeSpeciesController extends Controller
{
    public function __invoke(StoreTreeSpeciesRequest $storeTreeSpeciesRequest): TreeSpeciesResource
    {
        $model = $this->getEntityFromRequest($storeTreeSpeciesRequest);
        $this->authorize('read', $model);

        $storeTreeSpeciesRequest->merge([
            'speciesable_type' => get_class($model),
            'speciesable_id' => $model->id,
        ]);

        $treeSpecies = TreeSpecies::create($storeTreeSpeciesRequest->all());

        return new TreeSpeciesResource($treeSpecies);
    }

    private function getEntityFromRequest(StoreTreeSpeciesRequest $request)
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
