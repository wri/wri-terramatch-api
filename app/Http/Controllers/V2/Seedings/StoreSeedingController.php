<?php

namespace App\Http\Controllers\V2\Seedings;

use App\Exceptions\Terrafund\InvalidMorphableModelException;
use App\Http\Controllers\Controller;
use App\Http\Requests\V2\Seedings\StoreSeedingRequest;
use App\Http\Resources\V2\Seedings\SeedingResource;
use App\Models\V2\Sites\Site;
use App\Models\V2\Sites\SiteReport;

class StoreSeedingController extends Controller
{
    public function __invoke(StoreSeedingRequest $storeSeedingRequest): SeedingResource
    {
        $model = $this->getEntityFromRequest($storeSeedingRequest);
        $this->authorize('update', $model);

        $storeSeedingRequest->merge([
            'seedable_type' => get_class($model),
            'seedable_id' => $model->id,
        ]);

        $seeding = \App\Models\V2\Seeding::create($storeSeedingRequest->all());

        return new SeedingResource($seeding);
    }

    private function getEntityFromRequest(StoreSeedingRequest $request)
    {
        switch ($request->get('model_type')) {
            case 'site':
                return Site::isUuid($request->get('model_uuid'))->firstOrFail();
            case 'site-report':
                return SiteReport::isUuid($request->get('model_uuid'))->firstOrFail();

            default:
                throw new InvalidMorphableModelException();
        }
    }
}
