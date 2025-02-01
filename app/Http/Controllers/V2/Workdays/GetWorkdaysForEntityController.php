<?php

namespace App\Http\Controllers\V2\Workdays;

use App\Http\Controllers\Controller;
use App\Http\Resources\V2\Demographics\DemographicResource;
use App\Models\V2\Demographics\Demographic;
use App\Models\V2\Demographics\DemographicCollections;
use App\Models\V2\EntityModel;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class GetWorkdaysForEntityController extends Controller
{
    /**
     * @throws AuthorizationException
     * @throws \JsonException
     */
    public function __invoke(Request $request, EntityModel $entity)
    {
        $this->authorize('update', $entity);

        $workdays = $entity->workdays()->visible()->get();

        $expectedCollections = match ($entity->shortName) {
            'site-report' => array_keys(DemographicCollections::WORKDAYS_SITE_COLLECTIONS),
            'project-report' => array_keys(DemographicCollections::WORKDAYS_PROJECT_COLLECTIONS),
            default => throw new NotFoundHttpException(),
        };
        $collections = $workdays->pluck('collection');
        foreach ($expectedCollections as $collection) {
            if (! $collections->contains($collection)) {
                $workday = new Demographic();
                // Allows the resource to return an API response with no demographics, but still containing
                // the collection and readable collection name.
                $workday['type'] = Demographic::WORKDAY_TYPE;
                $workday['demographical_type'] = get_class($entity);
                $workday['collection'] = $collection;
                $workdays->push($workday);
            }
        }

        return DemographicResource::collection($workdays);
    }
}
